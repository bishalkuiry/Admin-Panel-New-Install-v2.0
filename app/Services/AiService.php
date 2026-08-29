<?php

namespace App\Services;

use App\Models\Setting;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class AiService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Get the active AI provider
     */
    public function getProvider(): string
    {
        return Setting::get('ai_provider', 'openai');
    }

    /**
     * Generate product details based on title
     */
    public function generateProductDetails(string $title, array $context = []): array
    {
        $provider = $this->getProvider();
        
        $systemPrompt = <<<'PROMPT'
You are an expert e-commerce product manager. Your task is to generate complete and accurate product details based on a product title.
The output MUST be a valid JSON object matching the requested schema.
Available Categories: %s
Available Attributes: %s
Attributes Status: %s
Available Units: %s

Rules:
1. Select the most appropriate category_id from the provided list.
2. Description should be professional, SEO-optimized, and detailed.
3. Suggest a realistic price and compare_price (MRP).
4. Suggest appropriate tags.
5. Select the most appropriate unit (one of the provided short_names).
6. Suggest an initial quantity in stock (default to a reasonable retail stock like 50).
7. Suggest product variants ONLY if it makes sense for the product AND if categories/attributes are available.
8. IMPORTANT: If the 'Available Attributes' list is empty, DO NOT generate any variants. 
9. For variants, you MUST map each variant characteristic to a provided attribute_id and value_id.
10. If an attribute name or value name is not in the provided list, skip that variant or suggest it without that attribute.
11. Include nutritional_info if it's a food product.
12. Provide a short_description.

JSON Schema:
{
    "name": string,
    "short_description": string,
    "description": string,
    "price": float,
    "compare_price": float,
    "category_id": int,
    "unit": string,
    "quantity": int,
    "tags": string[],
    "variants": [
        {
            "name": string,
            "selling_price": float,
            "mrp": float,
            "unit_value": string,
            "weight": float,
            "quantity": int,
            "attributes": [
                {
                    "attribute_id": int,
                    "attribute_name": string,
                    "value_id": int,
                    "value_name": string
                }
            ]
        }
    ],
    "nutritional_info": object
}
PROMPT;

        $systemPrompt = sprintf(
            $systemPrompt,
            json_encode($context['categories'] ?? []),
            json_encode($context['attributes'] ?? []),
            json_encode($context['attributes_status'] ?? 'Active attributes available'),
            json_encode($context['units'] ?? [])
        );

        $userPrompt = "Product Title: {$title}";

        return $this->callAi($systemPrompt, $userPrompt, true, [], $context['media'] ?? []);
    }

    /**
     * Chat with AI for product recommendations
     */
    public function chat(string $message, array $history = [], array $context = [], array $media = []): array
    {
        $provider = $this->getProvider();
        
        $systemPrompt = "You are an intelligent Personal Shopping Assistant for " . config('app.name', 'InAllCart') . ".
        You have access to the following REAL products from our database: " . json_encode($context['products'] ?? []) . "
        
        Your goals:
        1. Act like a helpful, knowledgeable sales associate.
        2. Suggest relevant items based on the user's message.
        3. ALWAYS use the 'ITEM_CARD' tag when mentioning a specific product, like this: [ITEM_CARD:123].
        4. Be enthusiastic but concise.
        5. If the user asks for suggestions, providing 2-3 item cards is better than a long list of text.
        6. IMPORTANT: ONLY recommend products from the list provided above. DO NOT HALLUCINATE OR INVENT PRODUCTS.
        7. If a product is not in the list, do not recommend it. Instead, suggest the closest match from the list or ask for more details.
        
        Current User Location/Context: " . json_encode($context['user'] ?? []) . "
        Available Categories: " . json_encode($context['categories'] ?? []);

        return $this->callAi($systemPrompt, $message, false, $history, $media);
    }

    /**
     * Internal method to call the selected AI provider
     */
    protected function callAi(string $systemPrompt, string $userPrompt, bool $jsonMode = false, array $history = [], array $media = []): array
    {
        $provider = $this->getProvider();
        
        if ($provider === 'openai') {
            return $this->callOpenAi($systemPrompt, $userPrompt, $jsonMode, $history, $media);
        } elseif ($provider === 'gemini') {
            return $this->callGemini($systemPrompt, $userPrompt, $jsonMode, $history, $media);
        }

        throw new \Exception("Invalid AI provider: {$provider}");
    }

    protected function callOpenAi(string $systemPrompt, string $userPrompt, bool $jsonMode, array $history, array $media = []): array
    {
        $apiKey = Setting::get('openai_api_key');
        $model = Setting::get('openai_model', 'gpt-4o');

        if (!$apiKey) {
            throw new \Exception("OpenAI API Key not configured.");
        }

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];

        foreach ($history as $msg) {
            $messages[] = $msg;
        }

        $userContent = [];
        if (!empty($media)) {
            foreach ($media as $item) {
                if (isset($item['type']) && $item['type'] === 'image' && isset($item['url'])) {
                    $userContent[] = ['type' => 'image_url', 'image_url' => ['url' => $item['url']]];
                }
            }
        }
        $userContent[] = ['type' => 'text', 'text' => $userPrompt];

        $messages[] = ['role' => 'user', 'content' => $userContent];

        try {
            $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $model,
                    'messages' => $messages,
                    'response_format' => $jsonMode ? ['type' => 'json_object'] : null,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $content = $data['choices'][0]['message']['content'];

            if ($jsonMode) {
                return json_decode($content, true);
            }

            return ['content' => $content];
        } catch (\Exception $e) {
            Log::error("OpenAI API Error: " . $e->getMessage());
            throw $e;
        }
    }

    protected function callGemini(string $systemPrompt, string $userPrompt, bool $jsonMode, array $history, array $media = []): array
    {
        $apiKey = Setting::get('gemini_api_key');
        $model = Setting::get('gemini_model', 'gemini-2.5-flash');

        if (!$apiKey) {
            throw new \Exception("Gemini API Key not configured.");
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $body = [
            'system_instruction' => [
                'parts' => [['text' => $systemPrompt]]
            ],
            'contents' => []
        ];

        foreach ($history as $msg) {
            $body['contents'][] = [
                'role' => $msg['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $msg['content']]]
            ];
        }

        $userParts = [];
        
        // Handle media for Gemini
        foreach ($media as $item) {
            if (isset($item['type']) && $item['type'] === 'image' && isset($item['data'])) {
                $userParts[] = [
                    'inline_data' => [
                        'mime_type' => $item['mime_type'] ?? 'image/jpeg',
                        'data' => $item['data'] // Expecting base64 string
                    ]
                ];
            } elseif (isset($item['type']) && $item['type'] === 'image' && isset($item['url'])) {
                // If it's a URL, we'd ideally download it or use file API, 
                // but for simplicity let's assume base64 is passed in 'data'
            }
        }
        
        $userParts[] = ['text' => $userPrompt];

        $body['contents'][] = [
            'role' => 'user',
            'parts' => $userParts
        ];

        if ($jsonMode) {
            $body['generationConfig'] = [
                'response_mime_type' => 'application/json',
            ];
        }

        try {
            $response = $this->client->post($url, [
                'json' => $body,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            if (isset($data['error'])) {
                throw new \Exception($data['error']['message'] ?? 'Gemini API Error');
            }

            $content = $data['candidates'][0]['content']['parts'][0]['text'];

            if ($jsonMode) {
                return json_decode($content, true);
            }

            return ['content' => $content];
        } catch (\Exception $e) {
            Log::error("Gemini API Error: " . $e->getMessage());
            throw $e;
        }
    }
    /**
     * Generate an image using the active AI provider
     */
    public function generateImage(string $prompt, ?string $referenceImage = null): ?string
    {
        // If we have a reference image, use Vision to describe it for better generation
        if ($referenceImage) {
            try {
                $productDescription = $this->describeImage($referenceImage, "Analyze this product image and describe it in extreme detail for a professional photography prompt. Focus on the product's shape, color, material, texture, and defining features. The goal is to recreate this EXACT product in a new professional setting.");
                if ($productDescription) {
                    $prompt = "Create a professional high-quality product photo of this product: " . $productDescription . ". " . $prompt;
                }
            } catch (\Exception $e) {
                Log::error("Vision description failed: " . $e->getMessage());
            }
        }

        $provider = $this->getProvider();
        $image = null;
        
        if ($provider === 'openai') {
            $image = $this->generateOpenAiImage($prompt);
        } elseif ($provider === 'gemini') {
            $image = $this->generateGeminiImage($prompt);
            
            // Fallback to OpenAI if Gemini image generation fails (often due to API availability)
            if (!$image && Setting::get('openai_api_key')) {
                Log::info("Gemini image generation failed or returned null. Attempting fallback to OpenAI DALL-E 3.");
                $image = $this->generateOpenAiImage($prompt);
            } elseif (!$image) {
                Log::warning("Gemini image generation failed and no OpenAI key is configured for fallback.");
            }
        }
        
        return $image;
    }

    /**
     * Describe an image using the multimodal model
     */
    public function describeImage(string $base64Image, string $instruction): ?string
    {
        // Clean base64 if needed
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
            $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
        }

        $context = [
            'media' => [
                [
                    'type' => 'image',
                    'data' => $base64Image,
                    'mime_type' => 'image/jpeg'
                ]
            ]
        ];
        
        try {
            // Use Gemini 2.0/2.5 Flash for vision as it's extremely fast and capable
            $history = [];
            $systemPrompt = "You are a professional product photographer and AI prompt engineer. Describe products accurately and aesthetically.";
            
            $response = $this->callGemini($systemPrompt, $instruction, false, $history, $context['media']);
            $content = $response['content'] ?? null;
            if ($content) {
                Log::info("Image described successfully: " . substr($content, 0, 100) . "...");
            }
            return $content;
        } catch (\Exception $e) {
            Log::error("Describe Image Error: " . $e->getMessage());
            return null;
        }
    }

    protected function generateOpenAiImage(string $prompt): ?string
    {
        $apiKey = Setting::get('openai_api_key');
        if (!$apiKey) return null;

        try {
            $response = $this->client->post('https://api.openai.com/v1/images/generations', [
                'headers' => [
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'dall-e-3',
                    'prompt' => $prompt,
                    'n' => 1,
                    'size' => '1024x1024',
                    'response_format' => 'b64_json',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['data'][0]['b64_json'] ?? null;
        } catch (\Exception $e) {
            Log::error("OpenAI Image Error: " . $e->getMessage());
            return null;
        }
    }

    protected function generateGeminiImage(string $prompt): ?string
    {
        $apiKey = Setting::get('gemini_api_key');
        if (!$apiKey) return null;

        // Choice 1: Native Generation Models (Newer multimodal models that can output images)
        $nativeModels = [
            'gemini-2.5-flash-image', 
            'gemini-2.5-flash', 
            'gemini-3-pro-preview',
            'gemini-2.0-flash-exp'
        ];

        foreach ($nativeModels as $model) {
            try {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
                
                // For native generation, we construct the request carefully.
                // We do NOT set response_mime_type to image/png because that is often for JSON output constraints.
                // Instead, we just ask for the image in the prompt and native logic handles it.
                $body = [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]]
                    ],
                    // 'generationConfig' => [ 'response_mime_type' => 'image/png' ] // REMOVED to fix 400 error
                ];

                $response = $this->client->post($url, [
                    'json' => $body,
                    'timeout' => 45,
                    'http_errors' => false
                ]);

                if ($response->getStatusCode() === 200) {
                    $data = json_decode($response->getBody()->getContents(), true);
                    foreach ($data['candidates'][0]['content']['parts'] ?? [] as $part) {
                        if (isset($part['inline_data']['data'])) {
                            Log::info("Native Gemini Image generated successfully using model: {$model}");
                            return $part['inline_data']['data'];
                        }
                    }
                } else {
                    $body = $response->getBody()->getContents();
                    Log::warning("Native Gemini Image Model {$model} failed (Status " . $response->getStatusCode() . "): " . substr($body, 0, 200));
                }
            } catch (\Exception $e) {
                Log::warning("Native Gemini Image Model {$model} failed: " . $e->getMessage());
            }
        }

        // Choice 2: Traditional Imagen Models
        $imagenModels = [
            'imagen-3.0-generate-001', 
            'imagen-3.0-flash-exp', 
            'imagen-3',
            'imagen-2.0-generate-001'
        ];
        
        foreach ($imagenModels as $model) {
            // Try both v1beta and v1 as some models are promoted
            $urls = [
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateImages?key={$apiKey}",
                "https://generativelanguage.googleapis.com/v1/models/{$model}:generateImages?key={$apiKey}"
            ];
            
            foreach ($urls as $url) {
                try {
                    $response = $this->client->post($url, [
                        'json' => [
                            'prompt' => $prompt,
                            'number_of_images' => 1,
                            'aspect_ratio' => '1:1',
                            'safety_setting' => 'BLOCK_LOW_AND_ABOVE',
                        ],
                        'timeout' => 45,
                        'http_errors' => false
                    ]);

                    $status = $response->getStatusCode();
                    $body = $response->getBody()->getContents();
                    
                    if ($status === 200) {
                        $data = json_decode($body, true);
                        if (isset($data['images'][0]['imageRawBytes'])) {
                            $raw = $data['images'][0]['imageRawBytes'];
                            Log::info("Gemini Imagen generated successfully using model: {$model}");
                            if (base64_encode(base64_decode($raw, true)) === $raw) {
                                return $raw;
                            }
                            return base64_encode($raw);
                        }
                    } else {
                        Log::warning("Gemini Imagen Model {$model} failed (Status {$status}): " . $body);
                    }
                } catch (\Exception $e) {
                    Log::warning("Gemini Imagen Model {$model} error: " . $e->getMessage());
                }
            }
        }
        
        Log::error("All Gemini image generation attempts (Native and Imagen) failed.");
        return null;
    }
}
