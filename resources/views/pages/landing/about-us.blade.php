<!-- About Us Section -->
<section id="about-us" class="about-section" style="position: relative; overflow: hidden; padding: 80px 0;">
    <!-- Background Decoration -->
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); z-index: -1;"></div>
    <div style="position: absolute; top: -100px; right: -100px; width: 400px; height: 400px; background: rgba(0, 77, 64, 0.05); border-radius: 50%; filter: blur(60px);"></div>
    <div style="position: absolute; bottom: -50px; left: -50px; width: 300px; height: 300px; background: rgba(255, 215, 0, 0.1); border-radius: 50%; filter: blur(50px);"></div>

    <div class="container">
        <!-- Company Intro -->
        <div class="row align-items-center mb-5">
            <div class="col-lg-6" data-aos="fade-right">
                <h4 style="color: #ffd700; font-weight: 600; letter-spacing: 2px; text-transform: uppercase;">Who We Are</h4>
                <h2 style="font-size: 2.5rem; font-weight: 800; color: #004d40; margin-bottom: 20px;">Pioneering Smart Solutions for Northern Nigeria</h2>
                <p style="font-size: 1.1rem; color: #555; line-height: 1.8;">
                    Arewa Smart Idea is more than just a tech company; we are a movement. Born from a passion to bridge the digital divide, we provide innovative, sustainable, and scalable technology services tailored for the unique challenges of our region.
                </p>
                <p style="font-size: 1.1rem; color: #555; line-height: 1.8;">
                    From seamless payments to digital identity management, we are building the infrastructure for a smarter tomorrow.
                </p>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="about-stats-card" style="background: white; padding: 40px; border-radius: 20px; box-shadow: 0 15px 40px rgba(0,0,0,0.08); border-left: 5px solid #ffd700;">
                    <div class="row text-center">
                        <div class="col-4">
                            <h3 style="font-size: 2.5rem; color: #004d40; font-weight: 700;">10+</h3>
                            <p style="color: #777; font-size: 0.9rem;">Years Experience</p>
                        </div>
                        <div class="col-4">
                            <h3 style="font-size: 2.5rem; color: #004d40; font-weight: 700;">500+</h3>
                            <p style="color: #777; font-size: 0.9rem;">Projects Done</p>
                        </div>
                        <div class="col-4">
                            <h3 style="font-size: 2.5rem; color: #004d40; font-weight: 700;">100%</h3>
                            <p style="color: #777; font-size: 0.9rem;">Satisfaction</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Section -->
        <div class="section-title text-center mb-5" data-aos="fade-up">
            <h2 style="color: #004d40; font-weight: 700;">Meet Our Visionaries</h2>
            <hr style="width: 60px; height: 3px; background: #ffd700; margin: 15px auto; border: none;">
            <p>The brilliant minds driving our mission forward.</p>
        </div>

        <div class="row">
            <!-- Team Member 1 -->
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="team-card">
                    <div class="team-img-wrapper">
                        <img src="{{ asset('assets/img/users/user-01.jpg') }}" alt="Muhammad Shafiu">
                        <div class="social-overlay">
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="team-info">
                        <h3>Muhammad Shafiu</h3>
                        <span>Founder & CEO</span>
                        <p>Visionary leader passionate about technology, innovation, and youth empowerment.</p>
                    </div>
                </div>
            </div>

            <!-- Team Member 2 -->
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="team-card">
                    <div class="team-img-wrapper">
                        <img src="{{ asset('assets/img/users/user-08.jpg') }}" alt="Umar Muhammad">
                        <div class="social-overlay">
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="team-info">
                        <h3>Umar Muhammad</h3>
                        <span>Head of Technology</span>
                        <p>Cloud infrastructure specialist ensuring secure and scalable platforms.</p>
                    </div>
                </div>
            </div>

            <!-- Team Member 3 -->
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="team-card">
                    <div class="team-img-wrapper">
                        <!-- Note: Using user-01.jpg as requested, but ideally should be unique -->
                        <img src="{{ asset('assets/img/users/user-55.jpg') }}" alt="Muhammad Sani Hamidu">
                        <div class="social-overlay">
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="team-info">
                        <h3>Muhammad Sani Hamidu</h3>
                        <span>Director of Operations</span>
                        <p>Bridging technology with strategy to maximize team performance.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .team-card {
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid rgba(0,0,0,0.05);
        }
        .team-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 77, 64, 0.15);
        }
        .team-img-wrapper {
            position: relative;
            overflow: hidden;
            height: 300px;
        }
        .team-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .team-card:hover .team-img-wrapper img {
            transform: scale(1.1);
        }
        .social-overlay {
            position: absolute;
            bottom: -50px;
            left: 0;
            width: 100%;
            background: rgba(0, 77, 64, 0.9);
            padding: 10px;
            display: flex;
            justify-content: center;
            transition: bottom 0.3s ease;
        }
        .team-card:hover .social-overlay {
            bottom: 0;
        }
        .social-overlay a {
            color: #ffd700;
            margin: 0 10px;
            font-size: 1.2rem;
            transition: color 0.3s;
        }
        .social-overlay a:hover {
            color: #fff;
        }
        .team-info {
            padding: 25px;
            text-align: center;
        }
        .team-info h3 {
            font-size: 1.4rem;
            font-weight: 700;
            color: #004d40;
            margin-bottom: 5px;
        }
        .team-info span {
            display: block;
            color: #ffd700;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 15px;
            text-transform: uppercase;
        }
        .team-info p {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 0;
        }
    </style>
</section>
<!-- End About Us Section -->
