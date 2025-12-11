<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Arewa Smart - {{ $title ?? 'Welcome to Arewa Smart Idea' }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{-- Add your custom assets for the registration page here if they aren't included by @vite --}}
        {{-- For this example, I'll assume your custom CSS is not managed by Vite and must be included separately --}}
        <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/logo/logo.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/apple-touch-icon.png') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/plugins/icons/feather/feather.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/plugins/tabler-icons/tabler-icons.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/fontawesome.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/all.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/landing.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    <!-- Open Graph / WhatsApp Meta Tags -->
    <meta property="og:title" content="Arewa Smart Idea - Innovative Digital Solutions">
    <meta property="og:description" content="Empowering northern Nigeria through innovative digital solutions and smart technology services.">
    <meta property="og:image" content="{{ asset('assets/img/logo/logo.png') }}">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:type" content="website">

    <style>
        /* Premium Design Enhancements */
        :root {
            --primary-color: #004d40;
            --accent-color: #ffd700;
        }

        /* Top Bar */
        .top-bar {
            background: linear-gradient(90deg, #1a1a1a, #000);
            color: #fff;
            padding: 10px 0;
            font-size: 0.9rem;
            border-bottom: 1px solid rgba(255,215,0,0.2);
        }
        .top-bar i {
            color: var(--accent-color);
            margin-right: 5px;
        }

        /* Hero Glassmorphism */
        .hero-content {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 3rem;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }

        /* WhatsApp Float */
        .whatsapp-float {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #25d366;
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            box-shadow: 0 4px 15px rgba(37, 211, 102, 0.4);
            z-index: 9999;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-decoration: none;
        }
        .whatsapp-float:hover {
            transform: scale(1.1) rotate(10deg);
            background: #128C7E;
            color: white;
        }

        /* Animations */
        @keyframes pulse-gold {
            0% { box-shadow: 0 0 0 0 rgba(255, 215, 0, 0.4); }
            70% { box-shadow: 0 0 0 15px rgba(255, 215, 0, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 215, 0, 0); }
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #004d40, #00695c);
            border: none;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 77, 64, 0.4);
        }
    </style>
    </head>

    <body class="bg-white">
        <div id="global-loader" style="display: none;">
            <div class="page-loader"></div>
        </div>

    <!-- Top Bar -->
    <div class="top-bar d-none d-md-block">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="contact-info">
                <span class="me-4"><i class="fas fa-map-marker-alt"></i> Sabuwar Kasuwa Street, Opposite Audi Residence, Zuru</span>
                <span><i class="fas fa-phone-alt"></i> 09110501995</span>
            </div>
            <div class="social-icons">
                <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                <a href="#" class="text-white"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header>
        <div class="container header-container">
            <a href="#" class="logo">
            <img src="{{ asset('assets/img/logo/logo.png') }}" alt="Arewa Smart Idea Logo" style="height: 40px; margin-right: 10px;">
            </a>
            <div class="mobile-menu">
            <i class="fas fa-bars"></i>
            </div>
            <nav>
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#partners">Partners</a></li>
                    <li><a href="#support">Support</a></li>
                    <li><a href="#about-us">About US</a></li>
                    <li><a href="https://zepaapi.com/">zepaapi</a></li>
                    <li><a href="{{route ('login')}}" class="btn btn-primary">Get Started</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="home" style="background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), 
         url('{{ asset('assets/img/landing/user4.png') }}') no-repeat center center/cover; min-height: 100vh; display: flex; align-items: center;">
        <div class="container hero-content text-center">
            <h1 class="text-white mb-4" data-aos="fade-down" data-aos-duration="1000" style="font-size: 3.5rem; font-weight: 700;">
                Welcome to Arewa Smart Idea
            </h1>
            <p class="text-white mb-5" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000" style="font-size: 1.25rem; max-width: 800px; margin: 0 auto;">
                Empowering northern Nigeria through innovative digital solutions and smart technology services. Join us in building a smarter, more connected future.
            </p>
            <div class="hero-btns" data-aos="fade-up" data-aos-delay="400" data-aos-duration="1000">
                <a href="{{route ('register')}}" class="btn btn-primary btn-lg me-3" style="padding: 12px 30px; border-radius: 30px; background: linear-gradient(45deg, #FF416C, #FF4B2B); border: none;">
                    Get Started
                </a>
                <a href="{{route ('login')}}" class="btn btn-secondary btn-lg me-3" style="padding: 12px 30px; border-radius: 30px; background: transparent; border: 2px solid #FF416C; color: #fff;">
                    Login Now
                </a>
            </div>
        </div>
    </section>

    <!-- Services Section -->
   @include('pages.landing.services')





   <!-- Testimonials Section -->
    <!-- Testimonials Section -->
    <section id="testimonials" class="testimonials-section" style="padding: 100px 0; background: linear-gradient(135deg, #004d40 0%, #00251a 100%); position: relative; overflow: hidden;">
        <!-- Background Patterns -->
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0.1; background-image: radial-gradient(#ffd700 1px, transparent 1px); background-size: 30px 30px;"></div>
        
        <div class="container" style="position: relative; z-index: 2;">
            <div class="section-title text-center mb-5" data-aos="fade-up">
                <h4 style="color: #ffd700; font-weight: 600; letter-spacing: 2px; text-transform: uppercase;">Testimonials</h4>
                <h2 style="color: #fff; font-weight: 800; font-size: 2.5rem;">Trusted by Leaders</h2>
                <hr style="width: 60px; height: 3px; background: #ffd700; margin: 15px auto; border: none;">
                <p class="text-white-50" style="max-width: 600px; margin: 0 auto; font-size: 1.1rem;">
                    See what our partners and clients have to say about their experience working with Arewa Smart Idea.
                </p>
            </div>

            <div class="row g-4">
                <!-- Testimonial 1 -->
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="testimonial-card-premium">
                        <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
                        <p class="review-text">“Arewa Smart transformed our operations with cutting-edge solutions. Their support team is always responsive and professional! Truly a game changer for our business.”</p>
                        <div class="reviewer-info">
                            <img src="{{ asset('assets/img/users/user-08.jpg') }}" alt="Abdulrahman Musa">
                            <div>
                                <h4>Abdulrahman Musa</h4>
                                <span>CEO, NorthernTech</span>
                                <div class="stars">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 2 -->
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="testimonial-card-premium">
                        <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
                        <p class="review-text">“Working with Arewa Smart has been a seamless experience. Their expertise and attention to detail are unmatched. They delivered exactly what we needed, on time.”</p>
                        <div class="reviewer-info">
                            <img src="{{ asset('assets/img/users/user-34.jpg') }}" alt="Fatima Bello">
                            <div>
                                <h4>Fatima Bello</h4>
                                <span>Manager, Arewa Logistics</span>
                                <div class="stars">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 3 -->
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="testimonial-card-premium">
                        <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
                        <p class="review-text">“The quality of service and support we’ve received from Arewa Smart is outstanding. Highly recommended for any business looking to scale digitally.”</p>
                        <div class="reviewer-info">
                            <img src="{{ asset('assets/img/users/user-01.jpg') }}" alt="Emeka Johnson">
                            <div>
                                <h4>Emeka Johnson</h4>
                                <span>IT Director, SmartLink Ltd</span>
                                <div class="stars">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .testimonial-card-premium {
                background: rgba(255, 255, 255, 0.05);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.1);
                padding: 40px 30px;
                border-radius: 20px;
                color: white;
                height: 100%;
                transition: all 0.3s ease;
                position: relative;
            }
            .testimonial-card-premium:hover {
                background: rgba(255, 255, 255, 0.1);
                transform: translateY(-5px);
            }
            .quote-icon {
                font-size: 2rem;
                color: #ffd700;
                margin-bottom: 20px;
                opacity: 0.5;
            }
            .review-text {
                font-size: 1.05rem;
                line-height: 1.8;
                margin-bottom: 30px;
                font-style: italic;
                opacity: 0.9;
            }
            .reviewer-info {
                display: flex;
                align-items: center;
            }
            .reviewer-info img {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                object-fit: cover;
                margin-right: 15px;
                border: 2px solid #ffd700;
            }
            .reviewer-info h4 {
                font-size: 1.1rem;
                font-weight: 700;
                margin-bottom: 2px;
                color: #fff;
            }
            .reviewer-info span {
                font-size: 0.85rem;
                color: rgba(255, 255, 255, 0.7);
                display: block;
                margin-bottom: 5px;
            }
            .stars {
                color: #ffd700;
                font-size: 0.8rem;
            }
        </style>
    </section>
<!-- End Testimonials Section -->

    <!-- about us -->
 @include('pages.landing.about-us')
     <!-- about us -->


    <!-- Partners Section -->
    <!-- Partners Section -->
    <section class="partners" id="partners" style="padding: 80px 0; background-color: #fff;">
        <div class="container">
            <div class="section-title text-center mb-5" data-aos="fade-up">
                <h4 style="color: #ffd700; font-weight: 600; letter-spacing: 2px; text-transform: uppercase;">Our Partners</h4>
                <h2 style="color: #004d40; font-weight: 800; font-size: 2.5rem;">Trusted Collaborations</h2>
                <hr style="width: 60px; height: 3px; background: #ffd700; margin: 15px auto; border: none;">
                <p class="text-muted" style="max-width: 600px; margin: 0 auto; font-size: 1.1rem;">
                    We proudly collaborate with leading organizations to deliver excellence and innovation.
                </p>
            </div>

            <div class="row align-items-center justify-content-center g-5">
                <div class="col-6 col-md-4 col-lg-2 text-center" data-aos="fade-up" data-aos-delay="100">
                    <img src="{{ asset('assets/img/partner/zepa.png') }}" alt="Zepa" class="partner-logo-premium">
                </div>
                <div class="col-6 col-md-4 col-lg-2 text-center" data-aos="fade-up" data-aos-delay="200">
                    <img src="{{ asset('assets/img/partner/biyanow.png') }}" alt="BiyaNow" class="partner-logo-premium">
                </div>
                <div class="col-6 col-md-4 col-lg-2 text-center" data-aos="fade-up" data-aos-delay="300">
                    <img src="{{ asset('assets/img/partner/palmpay.png') }}" alt="PalmPay" class="partner-logo-premium">
                </div>
                <div class="col-6 col-md-4 col-lg-2 text-center" data-aos="fade-up" data-aos-delay="400">
                    <img src="{{ asset('assets/img/partner/jamb.png') }}" alt="JAMB" class="partner-logo-premium">
                </div>
                <div class="col-6 col-md-4 col-lg-2 text-center" data-aos="fade-up" data-aos-delay="500">
                    <img src="{{ asset('assets/img/partner/nimc1.png') }}" alt="NIMC" class="partner-logo-premium">
                </div>
                <div class="col-6 col-md-4 col-lg-2 text-center" data-aos="fade-up" data-aos-delay="600">
                    <img src="{{ asset('assets/img/partner/bvnlogo.png') }}" alt="BVN" class="partner-logo-premium">
                </div>
            </div>
        </div>

        <style>
            .partner-logo-premium {
                max-width: 100%;
                height: auto;
                max-height: 60px;
                filter: grayscale(100%);
                opacity: 0.6;
                transition: all 0.4s ease;
            }
            .partner-logo-premium:hover {
                filter: grayscale(0%);
                opacity: 1;
                transform: scale(1.1);
            }
        </style>
    </section>

 @include('pages.landing.support')
    
    <!-- Footer -->
    <!-- Footer -->
    <footer style="background: #001a14; color: #fff; padding: 80px 0 30px; position: relative; overflow: hidden;">
        <!-- Background Decoration -->
        <div style="position: absolute; top: 0; right: 0; width: 300px; height: 300px; background: radial-gradient(circle, rgba(255,215,0,0.05) 0%, rgba(0,0,0,0) 70%);"></div>

        <div class="container">
            <div class="row g-5">
                <!-- Company Info -->
                <div class="col-lg-4 col-md-6">
                    <h2 style="color: #ffd700; font-weight: 700; margin-bottom: 20px;">Arewa Smart Idea</h2>
                    <p style="color: rgba(255,255,255,0.7); line-height: 1.8; margin-bottom: 25px;">
                        Providing innovative technology solutions to help businesses thrive in the digital world. We are committed to excellence and sustainable growth in Northern Nigeria.
                    </p>
                    <div class="social-links">
                        <a href="#" class="footer-social"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="footer-social"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="footer-social"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="footer-social"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6">
                    <h3 style="color: #fff; font-size: 1.2rem; font-weight: 600; margin-bottom: 25px; border-bottom: 2px solid #ffd700; display: inline-block; padding-bottom: 5px;">Quick Links</h3>
                    <ul class="footer-links list-unstyled">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#services">Services</a></li>
                        <li><a href="#partners">Partners</a></li>
                        <li><a href="#support">Support</a></li>
                        <li><a href="#about-us">About Us</a></li>
                    </ul>
                </div>
                
                <!-- Services -->
                <div class="col-lg-3 col-md-6">
                    <h3 style="color: #fff; font-size: 1.2rem; font-weight: 600; margin-bottom: 25px; border-bottom: 2px solid #ffd700; display: inline-block; padding-bottom: 5px;">Our Services</h3>
                    <ul class="footer-links list-unstyled">
                        <li><a href="#">Web Development</a></li>
                        <li><a href="#">Mobile Apps</a></li>
                        <li><a href="#">BVN & NIN Services</a></li>
                        <li><a href="#">Digital Marketing</a></li>
                        <li><a href="#">IT Consultancy</a></li>
                    </ul>
                </div>
                
                <!-- Contact -->
                <div class="col-lg-3 col-md-6">
                    <h3 style="color: #fff; font-size: 1.2rem; font-weight: 600; margin-bottom: 25px; border-bottom: 2px solid #ffd700; display: inline-block; padding-bottom: 5px;">Contact Us</h3>
                    <ul class="footer-contact list-unstyled">
                        <li style="margin-bottom: 15px; display: flex;">
                            <i class="fas fa-map-marker-alt" style="color: #ffd700; margin-top: 5px; margin-right: 10px;"></i>
                            <span style="color: rgba(255,255,255,0.8);">Sabuwar Kasuwa Street, Opposite Audi Residence, Zuru</span>
                        </li>
                        <li style="margin-bottom: 15px; display: flex;">
                            <i class="fas fa-phone" style="color: #ffd700; margin-top: 5px; margin-right: 10px;"></i>
                            <span style="color: rgba(255,255,255,0.8);">09110501995</span>
                        </li>
                        <li style="margin-bottom: 15px; display: flex;">
                            <i class="fas fa-envelope" style="color: #ffd700; margin-top: 5px; margin-right: 10px;"></i>
                            <span style="color: rgba(255,255,255,0.8);">info@arewasmarts.com</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <hr style="border-color: rgba(255,255,255,0.1); margin: 40px 0;">
            
            <div class="footer-bottom text-center">
                <p style="color: rgba(255,255,255,0.6); margin: 0;">&copy; 2024 Arewa Smart Idea. All rights reserved. | Designed with <i class="fas fa-heart" style="color: #ffd700;"></i> by Arewa Smart Team.</p>
            </div>
        </div>

        <style>
            .footer-links li {
                margin-bottom: 12px;
            }
            .footer-links a {
                color: rgba(255,255,255,0.7);
                text-decoration: none;
                transition: all 0.3s ease;
                display: inline-block;
            }
            .footer-links a:hover {
                color: #ffd700;
                transform: translateX(5px);
            }
            .footer-social {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 40px;
                height: 40px;
                background: rgba(255,255,255,0.1);
                color: #fff;
                border-radius: 50%;
                margin-right: 10px;
                text-decoration: none;
                transition: all 0.3s ease;
            }
            .footer-social:hover {
                background: #ffd700;
                color: #004d40;
                transform: translateY(-3px);
            }
        </style>
    </footer>

    <script>
        // Mobile menu toggle
        document.querySelector('.mobile-menu').addEventListener('click', function() {
            document.querySelector('nav ul').classList.toggle('active');
        });
        
        // Close mobile menu when clicking on a link
        document.querySelectorAll('nav ul li a').forEach(link => {
            link.addEventListener('click', function() {
                document.querySelector('nav ul').classList.remove('active');
            });
        });
        
        // Add scroll effect to header
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 100) {
                header.style.boxShadow = '0 5px 20px rgba(0,0,0,0.1)';
            } else {
                header.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
            }
        });
    </script>
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<script> AOS.init({ duration: 1000, once: true }) </script>

<!-- WhatsApp Floating Button -->
<a href="https://wa.me/2349110501995" class="whatsapp-float" target="_blank" title="Chat with us on WhatsApp">
    <i class="fab fa-whatsapp"></i>
</a>

</body>
</html>