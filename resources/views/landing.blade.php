    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>QuickMart - 10 Minute Grocery Delivery</title>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
        <style>
            /* --- Variables & Reset --- */
            :root {
                --primary: #FFC107;
                --primary-dark: #FFA000;
                --secondary: #0C831F;
                --secondary-light: #0d9b24;
                --dark: #1a1a1a;
                --darker: #121212;
                --light: #f8f9fa;
                --light-gray: #f5f5f5;
                --medium-gray: #e0e0e0;
                --text: #333333;
                --text-light: #666666;
                --white: #ffffff;
                --shadow-sm: 0 2px 8px rgba(0,0,0,0.05);
                --shadow: 0 8px 20px rgba(0,0,0,0.08);
                --shadow-lg: 0 15px 40px rgba(0,0,0,0.12);
                --radius-sm: 8px;
                --radius: 12px;
                --radius-lg: 20px;
                --transition: all 0.3s ease;
            }

            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: 'Manrope', sans-serif;
            }

            html {
                scroll-behavior: smooth;
            }

            body {
                background-color: var(--white);
                color: var(--text);
                line-height: 1.6;
                overflow-x: hidden;
            }

            a { 
                text-decoration: none; 
                color: inherit; 
            }
            
            ul { 
                list-style: none; 
            }

            img {
                max-width: 100%;
                height: auto;
                display: block;
            }

            /* --- Layout Utility --- */
            .container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 20px;
            }

            .section-padding {
                padding: 100px 0;
            }

            .section-padding-sm {
                padding: 70px 0;
            }

            .section-header {
                text-align: center;
                margin-bottom: 60px;
            }
            
            .section-header h2 {
                font-size: 2.8rem;
                margin-bottom: 15px;
                color: var(--dark);
                font-weight: 800;
            }
            
            .section-header p {
                color: var(--text-light);
                font-size: 1.2rem;
                max-width: 600px;
                margin: 0 auto;
            }

            .btn {
                display: inline-block;
                padding: 14px 32px;
                border-radius: 50px;
                font-weight: 700;
                font-size: 1rem;
                cursor: pointer;
                transition: var(--transition);
                text-align: center;
                border: none;
            }

            .btn-primary {
                background: var(--secondary);
                color: var(--white);
            }

            .btn-primary:hover {
                background: var(--secondary-light);
                transform: translateY(-3px);
                box-shadow: var(--shadow);
            }

            .btn-outline {
                background: transparent;
                color: var(--secondary);
                border: 2px solid var(--secondary);
            }

            .btn-outline:hover {
                background: var(--secondary);
                color: var(--white);
                transform: translateY(-3px);
                box-shadow: var(--shadow);
            }

            /* --- Navbar --- */
            .navbar {
                position: fixed;
                top: 0;
                width: 100%;
                background: rgba(255, 255, 255, 0.98);
                backdrop-filter: blur(10px);
                box-shadow: var(--shadow-sm);
                z-index: 1000;
                height: 80px;
                display: flex;
                align-items: center;
                transition: var(--transition);
            }

            .navbar.scrolled {
                height: 70px;
                box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            }

            .nav-container {
                display: flex;
                justify-content: space-between;
                align-items: center;
                width: 100%;
            }

            .logo {
                font-size: 1.8rem;
                font-weight: 800;
                color: var(--secondary);
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .logo i {
                color: var(--primary);
            }
            
            .logo span { 
                color: var(--primary); 
            }

            .nav-links {
                display: flex;
                gap: 35px;
                align-items: center;
            }

            .nav-links a {
                font-weight: 600;
                font-size: 1rem;
                position: relative;
                transition: var(--transition);
            }

            .nav-links a:not(.btn-download):hover {
                color: var(--secondary);
            }

            .nav-links a:not(.btn-download)::after {
                content: '';
                position: absolute;
                bottom: -5px;
                left: 0;
                width: 0;
                height: 2px;
                background: var(--secondary);
                transition: width 0.3s ease;
            }

            .nav-links a:not(.btn-download):hover::after {
                width: 100%;
            }

            .btn-download {
                background: var(--secondary);
                color: var(--white) !important;
                padding: 12px 28px;
                border-radius: 50px;
                font-weight: 700 !important;
                box-shadow: 0 4px 12px rgba(12, 131, 31, 0.2);
            }

            .btn-download:hover {
                background: var(--secondary-light);
                transform: translateY(-2px);
                box-shadow: 0 6px 18px rgba(12, 131, 31, 0.3);
            }

            .menu-toggle { 
                display: none; 
                font-size: 1.8rem; 
                cursor: pointer; 
                color: var(--dark);
            }

            /* --- Hero Section --- */
            .hero {
                padding-top: 160px;
                padding-bottom: 100px;
                background: linear-gradient(135deg, #f4fcf6 0%, #fff 100%);
                position: relative;
                overflow: hidden;
            }

            .hero::before {
                content: '';
                position: absolute;
                top: 0;
                right: 0;
                width: 40%;
                height: 100%;
                background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 Z" fill="%230C831F" opacity="0.03"/></svg>');
                background-size: cover;
                z-index: 0;
            }

            .hero-container {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 60px;
                align-items: center;
                position: relative;
                z-index: 1;
            }

            .hero-text h1 {
                font-size: 3.8rem;
                line-height: 1.1;
                margin-bottom: 25px;
                font-weight: 800;
                color: var(--dark);
            }

            .highlight { 
                color: var(--secondary); 
                position: relative;
                display: inline-block;
            }

            .highlight::after {
                content: '';
                position: absolute;
                bottom: 5px;
                left: 0;
                width: 100%;
                height: 8px;
                background: rgba(12, 131, 31, 0.15);
                z-index: -1;
                border-radius: 4px;
            }

            .hero-text p {
                font-size: 1.25rem;
                color: var(--text-light);
                margin-bottom: 40px;
                max-width: 90%;
            }

            .app-buttons { 
                display: flex; 
                gap: 20px; 
                flex-wrap: wrap;
            }

            .store-btn {
                display: flex;
                align-items: center;
                gap: 15px;
                background: var(--dark);
                color: var(--white);
                padding: 15px 25px;
                border-radius: var(--radius);
                transition: var(--transition);
                box-shadow: var(--shadow-sm);
                min-width: 180px;
            }

            .store-btn:hover { 
                transform: translateY(-5px); 
                box-shadow: var(--shadow);
            }
            
            .store-btn i { 
                font-size: 2rem; 
            }
            
            .store-btn div { 
                display: flex; 
                flex-direction: column; 
                line-height: 1.2; 
            }
            
            .store-btn span { 
                font-size: 0.75rem; 
                opacity: 0.9;
            }
            
            .store-btn strong { 
                font-size: 1.1rem; 
                font-weight: 700;
            }

            .hero-image {
                position: relative;
            }

            .hero-image img {
                width: 100%;
                border-radius: var(--radius-lg);
                box-shadow: var(--shadow-lg);
                transform: perspective(1000px) rotateY(-5deg) rotateX(2deg);
                transition: var(--transition);
            }

            .hero-image img:hover {
                transform: perspective(1000px) rotateY(0deg) rotateX(0deg);
            }

            .floating-badge {
                position: absolute;
                bottom: 30px;
                left: -20px;
                background: var(--white);
                padding: 15px 20px;
                border-radius: var(--radius);
                box-shadow: var(--shadow);
                display: flex;
                align-items: center;
                gap: 10px;
                animation: float 3s ease-in-out infinite;
            }

            .floating-badge i {
                font-size: 1.8rem;
                color: var(--primary);
            }

            .floating-badge div h4 {
                font-size: 1rem;
                margin-bottom: 3px;
            }

            .floating-badge div p {
                font-size: 0.9rem;
                color: var(--text-light);
            }

            @keyframes float {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-10px); }
            }

            /* --- Stats --- */
            .stats {
                background: var(--secondary);
                color: var(--white);
                padding: 70px 0;
            }
            
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                text-align: center;
                gap: 20px;
            }
            
            .stat-item h3 { 
                font-size: 3rem; 
                font-weight: 800; 
                margin-bottom: 10px;
            }
            
            .stat-item p { 
                opacity: 0.9; 
                font-size: 1.1rem;
            }

            /* --- Steps --- */
            .steps {
                position: relative;
            }

            .steps-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 30px;
            }

            .step-card {
                background: var(--white);
                padding: 40px 30px;
                border-radius: var(--radius);
                text-align: center;
                transition: var(--transition);
                border: 1px solid var(--medium-gray);
                position: relative;
                overflow: hidden;
            }

            .step-card:hover {
                box-shadow: var(--shadow);
                transform: translateY(-10px);
                border-color: transparent;
            }

            .step-number {
                position: absolute;
                top: 20px;
                left: 20px;
                width: 40px;
                height: 40px;
                background: var(--secondary);
                color: var(--white);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 800;
                font-size: 1.2rem;
            }

            .icon-box {
                width: 80px;
                height: 80px;
                background: rgba(12, 131, 31, 0.1);
                color: var(--secondary);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 2rem;
                margin: 0 auto 25px;
                transition: var(--transition);
            }

            .step-card:hover .icon-box {
                background: var(--secondary);
                color: var(--white);
            }

            /* --- Features --- */
            .features { 
                background: var(--light-gray); 
            }
            
            .features-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 30px;
            }
            
            .feature-card {
                background: var(--white);
                padding: 40px;
                border-radius: var(--radius);
                display: flex;
                gap: 25px;
                align-items: flex-start;
                box-shadow: var(--shadow-sm);
                transition: var(--transition);
                position: relative;
                overflow: hidden;
            }
            
            .feature-card:hover {
                box-shadow: var(--shadow);
                transform: translateY(-8px);
            }
            
            .feature-card i {
                font-size: 2.5rem;
                color: var(--primary);
                flex-shrink: 0;
            }

            .feature-card-content h3 {
                margin-bottom: 15px;
                font-size: 1.4rem;
            }

            /* --- Categories --- */
            .categories {
                background: var(--white);
            }

            .categories-grid {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 25px;
            }

            .category-card {
                background: var(--white);
                border-radius: var(--radius);
                overflow: hidden;
                box-shadow: var(--shadow-sm);
                transition: var(--transition);
                text-align: center;
                padding: 30px 20px;
                border: 1px solid var(--medium-gray);
            }

            .category-card:hover {
                transform: translateY(-8px);
                box-shadow: var(--shadow);
                border-color: var(--secondary);
            }

            .category-icon {
                width: 70px;
                height: 70px;
                background: rgba(12, 131, 31, 0.1);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 20px;
                font-size: 1.8rem;
                color: var(--secondary);
            }

            /* --- Testimonials --- */
            .testimonial-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 30px;
            }
            
            .testi-card {
                background: var(--white);
                border: 1px solid var(--medium-gray);
                padding: 40px;
                border-radius: var(--radius);
                position: relative;
                transition: var(--transition);
            }

            .testi-card:hover {
                box-shadow: var(--shadow);
                border-color: transparent;
            }

            .testi-card::before {
                content: '"';
                position: absolute;
                top: 20px;
                left: 20px;
                font-size: 4rem;
                color: rgba(12, 131, 31, 0.1);
                font-family: Georgia, serif;
                line-height: 1;
            }
            
            .testi-card p {
                font-size: 1.1rem;
                font-style: italic;
                color: var(--text);
                margin-bottom: 25px;
                position: relative;
                z-index: 1;
            }

            .user-info {
                display: flex;
                align-items: center;
                gap: 15px;
            }

            .user-avatar {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                background: var(--secondary);
                color: var(--white);
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
                font-size: 1.2rem;
            }
            
            .user-info h4 { 
                font-weight: 700; 
                margin-bottom: 5px;
            }
            
            .user-info span { 
                font-size: 0.9rem; 
                color: var(--text-light); 
            }

            /* --- FAQ --- */
            .faq-list {
                max-width: 900px;
                margin: 0 auto;
            }
            
            details {
                background: var(--white);
                margin-bottom: 15px;
                padding: 25px;
                border-radius: var(--radius-sm);
                border: 1px solid var(--medium-gray);
                cursor: pointer;
                transition: var(--transition);
            }
            
            details:hover {
                border-color: var(--secondary);
            }
            
            details[open] { 
                border-left: 4px solid var(--secondary); 
                box-shadow: var(--shadow-sm);
            }
            
            summary { 
                font-weight: 700; 
                font-size: 1.2rem; 
                list-style: none;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            summary::after {
                content: '+';
                font-size: 1.5rem;
                font-weight: 400;
                color: var(--secondary);
                transition: var(--transition);
            }
            
            details[open] summary::after {
                content: '-';
            }
            
            details p { 
                margin-top: 20px; 
                color: var(--text-light);
                line-height: 1.7;
                padding-right: 30px;
            }

            /* --- Download Banner --- */
            .download-banner {
                background: linear-gradient(135deg, var(--secondary) 0%, #0a6c1a 100%);
                padding: 100px 0;
                color: var(--white);
                overflow: hidden;
                position: relative;
            }

            .download-banner::before {
                content: '';
                position: absolute;
                top: 0;
                right: 0;
                width: 300px;
                height: 300px;
                background: rgba(255, 255, 255, 0.05);
                border-radius: 50%;
                transform: translate(30%, -30%);
            }

            .download-banner::after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 0;
                width: 200px;
                height: 200px;
                background: rgba(255, 255, 255, 0.05);
                border-radius: 50%;
                transform: translate(-30%, 30%);
            }

            .banner-content {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 60px;
                align-items: center;
                position: relative;
                z-index: 1;
            }
            
            .banner-text h2 { 
                font-size: 2.8rem; 
                margin-bottom: 20px; 
                font-weight: 800;
            }

            .banner-text p {
                font-size: 1.2rem;
                opacity: 0.9;
                margin-bottom: 35px;
            }
            
            .white-btn { 
                background: var(--white); 
                color: var(--dark) !important; 
                border: none;
            }

            .white-btn:hover {
                background: var(--light);
            }

            .banner-img img {
                border-radius: var(--radius-lg);
                box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
                transform: perspective(1000px) rotateY(5deg);
                transition: var(--transition);
            }

            .banner-img img:hover {
                transform: perspective(1000px) rotateY(0deg);
            }

            /* --- Footer --- */
            .footer {
                background: var(--darker);
                color: #aaa;
                padding: 80px 0 30px;
            }
            
            .footer-grid {
                display: grid;
                grid-template-columns: 1.5fr 1fr 1fr 1fr;
                gap: 50px;
                margin-bottom: 60px;
            }
            
            .footer-col h3 { 
                color: var(--white); 
                margin-bottom: 20px; 
                font-size: 1.8rem;
            }
            
            .footer-col h3 span { 
                color: var(--primary); 
            }
            
            .footer-col h4 { 
                color: var(--white); 
                margin-bottom: 25px; 
                font-size: 1.2rem;
            }
            
            .footer-col a { 
                display: block; 
                margin-bottom: 12px; 
                transition: var(--transition); 
            }   
            
            .footer-col a:hover { 
                color: var(--primary); 
                transform: translateX(5px);
            }

            .social-icons {
                display: flex;
                gap: 15px;
                margin-top: 20px;
            }

            .social-icons a {
                width: 40px;
                height: 40px;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: var(--transition);
            }

            .social-icons a:hover {
                background: var(--primary);
                color: var(--dark);
                transform: translateY(-3px);
            }

        .qr-box {
            text-align: center;
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: var(--radius);
            margin-top: 10px;
            }

            .qr-code {
            width: 140px;
            height: 140px;
            border-radius: 10px;
            margin-bottom: 15px;
            display: block;
            margin-left: auto;
            margin-right: auto;
            background: white;
            padding: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
            }

            .qr-code:hover {
            transform: scale(1.05);
            }

            .qr-box span {
            display: block;
            font-size: 0.9rem;
            color: #aaa;
            margin-top: 10px;
            }

            /* Mobile responsive adjustments */
            @media (max-width: 768px) {
            .qr-code {
            width: 120px;
            height: 120px;
            }
        }

            .footer-bottom {
                text-align: center;
                border-top: 1px solid #333;
                padding-top: 30px;
                font-size: 0.9rem;
                color: #888;
            }

            /* --- Mobile Responsive --- */
            @media (max-width: 1024px) {
                .hero-text h1 {
                    font-size: 3.2rem;
                }
                
                .section-header h2 {
                    font-size: 2.5rem;
                }
                
                .footer-grid {
                    grid-template-columns: repeat(2, 1fr);
                }
            }

            @media (max-width: 768px) {
                .navbar {
                    height: 70px;
                }
                
                .nav-links {
                    position: fixed;
                    top: 70px;
                    left: 0;
                    width: 100%;
                    background: var(--white);
                    flex-direction: column;
                    padding: 30px 20px;
                    display: none;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                    z-index: 999;
                }
                
                .nav-links.active { 
                    display: flex; 
                }
                
                .menu-toggle { 
                    display: block; 
                }

                .hero { 
                    padding-top: 120px;
                    padding-bottom: 80px;
                }
                
                .hero::before {
                    width: 60%;
                    opacity: 0.5;
                }
                
                .hero-container { 
                    grid-template-columns: 1fr; 
                    text-align: center; 
                    gap: 40px;
                }
                
                .hero-text { 
                    margin: 0 auto; 
                }
                
                .hero-text p {
                    max-width: 100%;
                }
                
                .app-buttons { 
                    justify-content: center; 
                }
                
                .floating-badge {
                    left: 50%;
                    transform: translateX(-50%);
                    bottom: -20px;
                }
                
                .stats-grid, 
                .steps-grid, 
                .features-grid, 
                .testimonial-grid, 
                .categories-grid,
                .banner-content {
                    grid-template-columns: 1fr;
                }
                
                .hero-text h1 { 
                    font-size: 2.5rem; 
                }
                
                .section-header h2 {
                    font-size: 2.2rem;
                }
                
                .section-padding {
                    padding: 70px 0;
                }
                
                .feature-card {
                    flex-direction: column;
                    text-align: center;
                    align-items: center;
                }
                
                .feature-card i {
                    margin-bottom: 15px;
                }
            }

            @media (max-width: 480px) {
                .hero-text h1 { 
                    font-size: 2.2rem; 
                }
                
                .section-header h2 {
                    font-size: 1.8rem;
                }
                
                .stat-item h3 {
                    font-size: 2.2rem;
                }
                
                .store-btn {
                    min-width: 100%;
                    justify-content: center;
                }
                
                .footer-grid {
                    grid-template-columns: 1fr;
                    gap: 40px;
                }
            }

            /* --- Animation --- */
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .fade-in {
                animation: fadeInUp 0.8s ease-out forwards;
            }
    
        /* Delivery Area Styles - Simplified */
        .delivery-area {
            background: linear-gradient(to bottom, #ffffff, #f9fcf9);
        }
        
        .delivery-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 50px;
        }
        
        .delivery-card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 30px 25px;
            text-align: center;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--medium-gray);
            transition: var(--transition);
            position: relative;
        }
        
        .delivery-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow);
            border-color: var(--secondary);
        }
        
        .city-icon {
            width: 70px;
            height: 70px;
            background: rgba(12, 131, 31, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 1.8rem;
            color: var(--secondary);
            transition: var(--transition);
        }
        
        .delivery-card:hover .city-icon {
            background: var(--secondary);
            color: var(--white);
        }
        
        .delivery-card h3 {
            font-size: 1.5rem;
            margin-bottom: 8px;
            color: var(--dark);
        }
        
        .delivery-card p {
            color: var(--text-light);
            margin-bottom: 15px;
            font-size: 0.95rem;
        }
        
        .status {
            display: inline-block;
            padding: 6px 15px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status.active {
            background: rgba(12, 131, 31, 0.1);
            color: var(--secondary);
        }
        
        .status:not(.active) {
            background: rgba(255, 193, 7, 0.1);
            color: var(--primary);
        }
        
        .coming-soon .city-icon {
            background: rgba(255, 193, 7, 0.1);
            color: var(--primary);
        }
        
        .coming-soon:hover .city-icon {
            background: var(--primary);
            color: var(--white);
        }
        
        .request-card {
            border: 2px dashed var(--medium-gray);
            background: rgba(12, 131, 31, 0.02);
        }
        
        .request-card:hover {
            border-color: var(--secondary);
            background: rgba(12, 131, 31, 0.05);
        }
        
        .request-card .city-icon {
            background: rgba(12, 131, 31, 0.05);
            color: var(--secondary);
        }
        
        .btn-request {
            background: var(--secondary);
            color: var(--white);
            border: none;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 10px;
            width: 100%;
        }
        
        .btn-request:hover {
            background: var(--secondary-light);
            transform: translateY(-2px);
        }
        
        .delivery-stats {
            display: flex;
            justify-content: center;
            gap: 40px;
            flex-wrap: wrap;
            padding-top: 40px;
            border-top: 1px solid var(--medium-gray);
        }
        
        .delivery-stats .stat-item {
            text-align: center;
        }
        
        .delivery-stats h3 {
            font-size: 2.5rem;
            color: var(--secondary);
            margin-bottom: 5px;
            font-weight: 800;
        }
        
        .delivery-stats p {
            color: var(--text-light);
            font-size: 0.95rem;
        }
        
        /* Partnership Styles - Simplified */
        .partner-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }
        
        .partner-card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 40px 30px;
            box-shadow: var(--shadow);
            border: 1px solid var(--medium-gray);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
        }
        
        .partner-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
            border-color: var(--secondary);
        }
        
        .partner-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 25px;
            border-bottom: 1px solid var(--light-gray);
        }
        
        .partner-icon {
            width: 80px;
            height: 80px;
            background: rgba(12, 131, 31, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.2rem;
            color: var(--secondary);
        }
        
        .partner-card:nth-child(2) .partner-icon {
            background: rgba(255, 193, 7, 0.1);
            color: var(--primary);
        }
        
        .partner-card h3 {
            font-size: 1.8rem;
            margin-bottom: 8px;
            color: var(--dark);
        }
        
        .partner-subtitle {
            color: var(--text-light);
            font-size: 1rem;
        }
        
        .partner-benefits {
            flex: 1;
            margin-bottom: 30px;
        }
        
        .benefit-item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .benefit-item i {
            font-size: 1.5rem;
            color: var(--secondary);
            margin-top: 5px;
            flex-shrink: 0;
        }
        
        .partner-card:nth-child(2) .benefit-item i {
            color: var(--primary);
        }
        
        .benefit-item h4 {
            font-size: 1.1rem;
            margin-bottom: 5px;
            color: var(--dark);
        }
        
        .benefit-item p {
            color: var(--text-light);
            font-size: 0.95rem;
            line-height: 1.5;
        }
        
        .partner-cta {
            text-align: center;
        }
        
        .partner-cta .btn {
            width: 100%;
            padding: 15px;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .partner-bottom {
            background: rgba(12, 131, 31, 0.05);
            border-radius: var(--radius);
            padding: 40px;
            text-align: center;
            border: 1px solid rgba(12, 131, 31, 0.1);
        }
        
        .partner-contact h4 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: var(--dark);
        }
        
        .partner-contact > p {
            color: var(--text-light);
            margin-bottom: 20px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .contact-info {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }
        
        .contact-info p {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text);
            font-weight: 500;
        }
        
        .contact-info i {
            color: var(--secondary);
            font-size: 1.1rem;
        }
        
        /* Responsive Styles */
        @media (max-width: 768px) {
            .delivery-cards {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
            }
            
            .partner-cards {
                grid-template-columns: 1fr;
                gap: 25px;
            }
            
            .partner-card {
                padding: 30px 25px;
            }
            
            .delivery-stats {
                gap: 30px;
            }
            
            .delivery-stats h3 {
                font-size: 2rem;
            }
            
            .contact-info {
                flex-direction: column;
                gap: 15px;
            }
        }
        
        @media (max-width: 480px) {
            .delivery-card {
                padding: 25px 20px;
            }
            
            .delivery-cards {
                grid-template-columns: 1fr;
            }
            
            .delivery-stats {
                flex-direction: column;
                gap: 25px;
            }
            
            .partner-bottom {
                padding: 30px 20px;
            }
        }

        </style>
    </head>
    <body>
        <!-- Navigation -->
        <nav class="navbar">
            <div class="container nav-container">
                <a href="#" class="logo">
                    <i class="fas fa-shopping-basket"></i> Quick<span>Mart</span>.
                </a>
                <div class="nav-links">
                    <a href="#home">Home</a>
                    <a href="#how-it-works">How It Works</a>
                    <a href="#features">Features</a>
                    <a href="#categories">Categories</a>
                    <a href="#testimonials">Testimonials</a>
                    <a href="#faq">FAQ</a>
                    <a href="#" class="btn-download">Download App</a>
                </div>
                <div class="menu-toggle"><i class="fas fa-bars"></i></div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="hero" id="home">
            <div class="container hero-container">
                <div class="hero-text fade-in">
                    <h1>Groceries delivered in <span class="highlight">10 minutes</span>, not hours.</h1>
                    <p>From fresh vegetables to daily essentials. Get everything you need delivered to your doorstep instantly. 5000+ items from local stores near you.</p>
                    <div class="app-buttons">
                        <a href="#" class="store-btn">
                            <i class="fab fa-apple"></i>
                            <div>
                                <span>Download on the</span>
                                <strong>App Store</strong>
                            </div>
                        </a>
                        <a href="#" class="store-btn">
                            <i class="fab fa-google-play"></i>
                            <div>
                                <span>GET IT ON</span>
                                <strong>Google Play</strong>
                            </div>
                        </a>
                    </div>
                    <div class="hero-cta" style="margin-top: 40px;">
                        <a href="#how-it-works" class="btn btn-outline">See How It Works</a>
                    </div>
                </div>
                <div class="hero-image fade-in">
                    <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Grocery App Mobile Interface">
                    <div class="floating-badge">
                        <i class="fas fa-bolt"></i>
                        <div>
                            <h4>10-min Delivery</h4>
                            <p>Guaranteed in selected areas</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="stats">
            <div class="container stats-grid">
                <div class="stat-item">
                    <h3>10k+</h3>
                    <p>Happy Shoppers</p>
                </div>
                <div class="stat-item">
                    <h3>5000+</h3>
                    <p>Daily Orders</p>
                </div>
                <div class="stat-item">
                    <h3>1000+</h3>
                    <p>Delivery Partners</p>
                </div>
                <div class="stat-item">
                    <h3>10 min</h3>
                    <p>Avg Delivery Time</p>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section class="steps section-padding" id="how-it-works">
            <div class="container">
                <div class="section-header">
                    <h2>Order in 3 Simple Steps</h2>
                    <p>Fresh groceries are just a few taps away. Our process is designed to be simple and fast.</p>
                </div>
                <div class="steps-grid">
                    <div class="step-card fade-in">
                        <div class="step-number">1</div>
                        <div class="icon-box"><i class="fas fa-map-marker-alt"></i></div>
                        <h3>Set Location</h3>
                        <p>Choose your current location to see available products and delivery times near you.</p>
                    </div>
                    <div class="step-card fade-in">
                        <div class="step-number">2</div>
                        <div class="icon-box"><i class="fas fa-shopping-basket"></i></div>
                        <h3>Add to Cart</h3>
                        <p>Browse thousands of items from veggies to snacks and add them to your basket.</p>
                    </div>
                    <div class="step-card fade-in">
                        <div class="step-number">3</div>
                        <div class="icon-box"><i class="fas fa-motorcycle"></i></div>
                        <h3>Fast Delivery</h3>
                        <p>Relax! Our delivery partner will be at your door before you finish your coffee.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features section-padding" id="features">
            <div class="container">
                <div class="section-header">
                    <h2>The Best Experience</h2>
                    <p>Why thousands choose QuickMart for their daily needs.</p>
                </div>
                <div class="features-grid">
                    <div class="feature-card fade-in">
                        <i class="fas fa-tags"></i>
                        <div class="feature-card-content">
                            <h3>Best Prices & Offers</h3>
                            <p>Cheaper than your local supermarket. We match or beat market prices daily with exclusive app-only discounts.</p>
                        </div>
                    </div>
                    <div class="feature-card fade-in">
                        <i class="fas fa-leaf"></i>
                        <div class="feature-card-content">
                            <h3>Farm Fresh Quality</h3>
                            <p>Fruits and vegetables are sourced directly from farmers and checked for quality before delivery.</p>
                        </div>
                    </div>
                    <div class="feature-card fade-in">
                        <i class="fas fa-clock"></i>
                        <div class="feature-card-content">
                            <h3>Open 6 AM - 12 AM</h3>
                            <p>Early morning milk or late-night munchies? We are open when you need us, 18 hours a day.</p>
                        </div>
                    </div>
                    <div class="feature-card fade-in">
                        <i class="fas fa-headset"></i>
                        <div class="feature-card-content">
                            <h3>Instant Support</h3>
                            <p>Have an issue with an item? Chat with us and get an instant refund or replacement within minutes.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Categories Section -->
        <section class="categories section-padding-sm" id="categories">
            <div class="container">
                <div class="section-header">
                    <h2>Shop By Category</h2>
                    <p>Everything you need, organized for easy shopping.</p>
                </div>
                <div class="categories-grid">
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-carrot"></i>
                        </div>
                        <h3>Fresh Vegetables</h3>
                        <p>Farm-fresh veggies delivered daily</p>
                    </div>
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-apple-alt"></i>
                        </div>
                        <h3>Fruits</h3>
                        <p>Seasonal & exotic fruits</p>
                    </div>
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-wine-bottle"></i>
                        </div>
                        <h3>Beverages</h3>
                        <p>Soft drinks, juices & more</p>
                    </div>
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-cookie"></i>
                        </div>
                        <h3>Snacks</h3>
                        <p>Chips, chocolates & biscuits</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="testimonials section-padding" id="testimonials">
            <div class="container">
                <div class="section-header">
                    <h2>What people are saying</h2>
                    <p>Real feedback from our happy customers.</p>
                </div>
                <div class="testimonial-grid">
                    <div class="testi-card fade-in">
                        <p>"QuickMart changed my life. I don't need to step out for milk or bread anymore. It's literally 10 minutes! The quality is always fresh and prices are reasonable."</p>
                        <div class="user-info">
                            <div class="user-avatar">PS</div>
                            <div>
                                <h4>Priya Sharma</h4>
                                <span>Homemaker, Patna</span>
                            </div>
                        </div>
                    </div>
                    <div class="testi-card fade-in">
                        <p>"The app is so smooth and the interface is just like Blinkit but faster. Love the dark mode option too. I use it almost daily for my grocery needs."</p>
                        <div class="user-info">
                            <div class="user-avatar">RV</div>
                            <div>
                                <h4>Rahul Verma</h4>
                                <span>Software Engineer, Bangalore</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>


    <!-- Delivery Area Section - Simplified -->
    <section class="delivery-area section-padding">
        <div class="container">
            <div class="section-header">
                <h2>Our Delivery Network</h2>
                <p>Currently serving major cities with lightning-fast 10-minute delivery</p>
            </div>
            
            <div class="delivery-cards">
                <div class="delivery-card">
                    <div class="city-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3>Patna</h3>
                    <p>10-15 min delivery</p>
                    <div class="status active">Active</div>
                </div>
                
                <div class="delivery-card">
                    <div class="city-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3>Delhi</h3>
                    <p>10-12 min delivery</p>
                    <div class="status active">Active</div>
                </div>
                
                <div class="delivery-card coming-soon">
                    <div class="city-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3>Chennai</h3>
                    <p>Launching Soon</p>
                    <div class="status">Coming Soon</div>
                </div>
                
                <div class="delivery-card request-card">
                    <div class="city-icon">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <h3>Your City?</h3>
                    <p>Request QuickMart in your area</p>
                    <button class="btn-request">Request City</button>
                </div>
            </div>
            
            <div class="delivery-stats">
                <div class="stat-item">
                    <h3>50+</h3>
                    <p>Service Areas</p>
                </div>
                <div class="stat-item">
                    <h3>10 min</h3>
                    <p>Average Delivery Time</p>
                </div>
                <div class="stat-item">
                    <h3>98%</h3>
                    <p>On-Time Delivery Rate</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Partnership Section - Simplified -->
    <section class="partnership section-padding" id="partnership">
        <div class="container">
            <div class="section-header">
                <h2>Partner With QuickMart</h2>
                <p>Join our growing network and be part of India's fastest grocery delivery revolution</p>
            </div>
            
            <div class="partner-cards">
                <!-- Delivery Partner Card -->
                <div class="partner-card">
                    <div class="partner-header">
                        <div class="partner-icon">
                            <i class="fas fa-motorcycle"></i>
                        </div>
                        <h3>Delivery Partner</h3>
                        <p class="partner-subtitle">Earn on your schedule</p>
                    </div>
                    
                    <div class="partner-benefits">
                        <div class="benefit-item">
                            <i class="fas fa-rupee-sign"></i>
                            <div>
                                <h4>Good Earnings</h4>
                                <p>Competitive delivery fees and incentives</p>
                            </div>
                        </div>
                        
                        <div class="benefit-item">
                            <i class="fas fa-calendar-alt"></i>
                            <div>
                                <h4>Flexible Hours</h4>
                                <p>Work whenever you want - full time or part time</p>
                            </div>
                        </div>
                        
                        <div class="benefit-item">
                            <i class="fas fa-shield-alt"></i>
                            <div>
                                <h4>Safe & Secure</h4>
                                <p>Insurance coverage and safety support</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="partner-cta">
                        <a href="#" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Join as Delivery Partner
                        </a>
                    </div>
                </div>
                
                <!-- Store Partner Card -->
                <div class="partner-card">
                    <div class="partner-header">
                        <div class="partner-icon">
                            <i class="fas fa-store"></i>
                        </div>
                        <h3>Store Partner</h3>
                        <p class="partner-subtitle">Grow your business</p>
                    </div>
                    
                    <div class="partner-benefits">
                        <div class="benefit-item">
                            <i class="fas fa-chart-line"></i>
                            <div>
                                <h4>Increase Sales</h4>
                                <p>Reach more customers in your area</p>
                            </div>
                        </div>
                        
                        <div class="benefit-item">
                            <i class="fas fa-truck"></i>
                            <div>
                                <h4>Zero Delivery Cost</h4>
                                <p>We handle all delivery operations</p>
                            </div>
                        </div>
                        
                        <div class="benefit-item">
                            <i class="fas fa-chart-bar"></i>
                            <div>
                                <h4>Business Insights</h4>
                                <p>Get detailed analytics and reports</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="partner-cta">
                        <a href="#" class="btn btn-primary">
                            <i class="fas fa-store"></i> Register Your Store
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="partner-bottom">
                <div class="partner-contact">
                    <h4>Have Questions?</h4>
                    <p>Contact our partnership team for more details</p>
                    <div class="contact-info">
                        <p><i class="fas fa-phone"></i> +91 98765 43210</p>
                        <p><i class="fas fa-envelope"></i> partners@quickmart.in</p>
                    </div>
                </div>
            </div>
        </div>
    </section>





        <!-- FAQ Section -->
        <section class="faq section-padding" id="faq">
            <div class="container">
                <div class="section-header">
                    <h2>Frequently Asked Questions</h2>
                    <p>Got questions? We've got answers.</p>
                </div>
                <div class="faq-list">
                    <details open class="fade-in">
                        <summary>How fast is the delivery?</summary>
                        <p>We aim to deliver within 10 to 15 minutes for most locations within our service radius. For locations slightly farther, delivery takes 15-25 minutes. You can track your delivery partner in real-time through our app.</p>
                    </details>
                    <details class="fade-in">
                        <summary>Is there a minimum order value?</summary>
                        <p>No, there is no minimum order value! We believe in serving all your needs, big or small. Delivery fees may apply for very small orders (below ₹99) during peak hours.</p>
                    </details>
                    <details class="fade-in">
                        <summary>How do I pay?</summary>
                        <p>We accept UPI, Credit/Debit Cards, Net Banking, and Cash on Delivery. For a seamless experience, we recommend using our in-app payment options which are faster and more secure.</p>
                    </details>
                    <details class="fade-in">
                        <summary>What cities are you available in?</summary>
                        <p>We are currently operating in Patna, Delhi, Mumbai, and Bangalore. We're expanding rapidly to new cities every month. Check our app to see if we've launched in your city.</p>
                    </details>
                    <details class="fade-in">
                        <summary>What if I receive a damaged item?</summary>
                        <p>We have a 100% satisfaction guarantee. If you receive a damaged or unsatisfactory item, simply report it in the app within 10 minutes of delivery for an instant refund or replacement.</p>
                    </details>
                </div>
            </div>
        </section>

        <!-- Download Banner -->
        <section class="download-banner">
            <div class="container banner-content">
                <div class="banner-text fade-in">
                    <h2>The Future of Grocery Shopping is Here.</h2>
                    <p>Experience seamless shopping, real-time tracking, and exclusive deals. Download the app and elevate your grocery shopping experience.</p>
                    <div class="app-buttons">
                        <a href="#" class="store-btn white-btn">
                            <i class="fab fa-apple"></i>
                            <div><span>Download on</span><strong>App Store</strong></div>
                        </a>
                        <a href="#" class="store-btn white-btn">
                            <i class="fab fa-google-play"></i>
                            <div><span>GET IT ON</span><strong>Google Play</strong></div>
                        </a>
                    </div>
                </div>
                <div class="banner-img fade-in">
                    <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="App Preview">
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="footer" id="contact">
            <div class="container">
                <div class="footer-grid">
                    <div class="footer-col">
                        <h3>Quick<span>Mart</span>.</h3>
                        <p>Patna, Bihar 800001<br>+91 9876543210<br>support@quickmart.in</p>
                        <div class="social-icons">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                    <div class="footer-col">
                        <h4>Company</h4>
                        <a href="#">About Us</a>
                        <a href="#">Careers</a>
                        <a href="#">Blog</a>
                        <a href="#">Press</a>
                        <a href="#">Partner with Us</a>
                    </div>
                    <div class="footer-col">
                        <h4>Legal</h4>
                        <a href="#">Privacy Policy</a>
                        <a href="#">Terms of Service</a>
                        <a href="#">Cookie Policy</a>
                        <a href="#">Return Policy</a>
                    </div>
                <div class="footer-col">
        <h4>Get the App</h4>
        <div class="qr-box">
            <img src="https://api.qrserver.com/v1/create-qr-code/?data=https%3A%2F%2Fplay.google.com%2F&size=220x220&margin=0" 
                alt="QR Code for Google Play Store"
                class="qr-code">
            <span>Scan to Download</span>
        </div>
    </div>
                </div>
                <div class="footer-bottom">
                    <p>&copy; 2025 QuickMart Technologies. All Rights Reserved.</p>
                </div>
            </div>
        </footer>

        <script>
            // Mobile Menu Toggle
            const menuToggle = document.querySelector('.menu-toggle');
            const navLinks = document.querySelector('.nav-links');
            const navbar = document.querySelector('.navbar');
            
            menuToggle.addEventListener('click', () => {
                navLinks.classList.toggle('active');
                menuToggle.querySelector('i').classList.toggle('fa-bars');
                menuToggle.querySelector('i').classList.toggle('fa-times');
            });
            
            // Navbar scroll effect
            window.addEventListener('scroll', () => {
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });
            
            // Close mobile menu when clicking a link
            document.querySelectorAll('.nav-links a').forEach(link => {
                link.addEventListener('click', () => {
                    navLinks.classList.remove('active');
                    menuToggle.querySelector('i').classList.add('fa-bars');
                    menuToggle.querySelector('i').classList.remove('fa-times');
                });
            });
            
            // FAQ toggle functionality
            document.querySelectorAll('details').forEach(detail => {
                detail.addEventListener('toggle', () => {
                    if (detail.open) {
                        // Close other open details
                        document.querySelectorAll('details').forEach(otherDetail => {
                            if (otherDetail !== detail && otherDetail.open) {
                                otherDetail.open = false;
                            }
                        });
                    }
                });
            });
            
            // Add fade-in animation to elements when they come into view
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('fade-in');
                    }
                });
            }, observerOptions);
            
            // Observe elements with fade-in class
            document.querySelectorAll('.fade-in').forEach(el => {
                observer.observe(el);
            });
            
            // Initialize all elements with fade-in class
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('.fade-in').forEach(el => {
                    el.style.opacity = '0';
                });
            });
        </script>

        <script>
        // City request functionality
        document.querySelectorAll('.btn-request').forEach(btn => {
            btn.addEventListener('click', function() {
                const city = prompt("Enter your city name to request QuickMart service:");
                if (city) {
                    alert(`Thank you for requesting QuickMart in ${city}! We'll notify you when we launch there.`);
                    
                    // Update the request card
                    const card = this.closest('.request-card');
                    card.innerHTML = `
                        <div class="city-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3>Request Sent!</h3>
                        <p>We'll notify you when we launch in ${city}</p>
                        <div class="status active">Requested</div>
                    `;
                    
                    // Add fade-in animation
                    card.style.animation = 'fadeIn 0.5s ease';
                }
            });
        });
        
        // Smooth scroll to partnership section
        document.querySelectorAll('a[href="#partnership"]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('partnership').scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
        
        // Animation for cards on scroll
        const cardObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });
        
        // Observe all delivery and partner cards
        document.querySelectorAll('.delivery-card, .partner-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            cardObserver.observe(card);
        });
    </script>
    </body>
    </html>