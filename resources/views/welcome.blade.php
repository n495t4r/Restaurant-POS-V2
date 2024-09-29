<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PEVA Technologies - Empowering Data-Driven Business Growth</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #000000;
            color: rgba(255, 255, 255, 0.7);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
        }

        nav a {
            color: rgba(255, 255, 255, 0.4);
            text-decoration: none;
            margin-left: 20px;
        }

        .sign-up {
            background-color: #ffffff;
            color: #000000;
            padding: 10px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: bold;
        }

        .hero {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 50px 0;
        }

        .hero-text {
            width: 50%;
        }

        .hero h1 {
            font-size: 48px;
            margin-bottom: 10px;
            line-height: 1.2;
            word-wrap: break-word;
            max-width: 100%;
        }

        .hero p {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.4);
        }

        .stats {
            font-size: 24px;
            margin-top: 20px;
            display: flex;
            align-items: center;
        }

        .stats .highlight {
            font-size: 64px;
            line-height: 1;
            margin-right: 10px;
        }

        .stats .satisfied {
            display: block;
            font-size: 12px;
            margin-bottom: -5px;
        }

        .stats .customers {
            display: block;
            font-size: 12px;
            margin-top: -5px;
        }

        .hero-image {
            width: 50%;
            text-align: right;
        }

        .features {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
            margin-left: 80px;
        }

        .feature-text {
            width: 40%;
            font-size: 15px;
            font-family: 'Courier New', Courier, monospace;
            font-weight: 300;
            line-height: 40px;
            margin-left: 12px;
        }

        .highlight-orange {
            color: orange;
            border: 1px solid orange;
            padding: 5px;
            padding-top: 1px;
            padding-bottom: 1px;
            border-radius: 18px;
            background-color: transparent;
        }

        .highlight-blue {
            color: skyblue;
            border: 1px solid skyblue;
            padding: 5px;
            padding-top: 1px;
            padding-bottom: 1px;
            border-radius: 18px;
            background-color: transparent;
        }

        .highlight-green {
            color: lightgreen;
            border: 1px solid lightgreen;
            padding: 5px;
            padding-top: 1px;
            padding-bottom: 1px;
            border-radius: 18px;
            background-color: transparent;
        }

        .feature-cards {
            width: 50%;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.05);
            border: 0.5px solid gray;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            width: calc(50% - 10px);
            box-sizing: border-box;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .feature-card-icon {
            font-size: 40px;
            margin-bottom: 15px;
        }

        .feature-card h3 {
            font-size: 18px;
            font-weight: normal;
            margin: 0 0 10px 0;
        }

        .feature-card p {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.4);
            margin: 0;
        }

        .slideshow {
            text-align: center;
            margin-top: 50px;
        }

        .slideshow img {
            width: 100%;
            max-width: 800px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
        }

        .glow-effect {
            position: absolute;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(0, 0, 255, 0.2) 0%, rgba(255, 0, 255, 0.2) 50%, rgba(0, 0, 0, 0) 70%);
            filter: blur(40px);
            z-index: -1;
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <div class="logo">PEVA</div>
            <nav>
                <a href="#about">About</a>
                <a href="#features">Features</a>
                <a href="#faq">FAQ</a>
                <a href="#login">Log in</a>
                <a href="#signup" class="sign-up">Sign Up →</a>
            </nav>
        </header>

        <!-- Section 1: Hero Section -->
        <section class="hero">
            <div class="hero-text">
                <h1>Automate business processes through innovative solutions</h1>
                <h1>AI insight to your business data</h1>
                <p>We provide you with reliable application solutions</p>
                <!-- <p>Automate business processes through innovative solutions</p> -->
                <div class="stats">
                    <!-- <span class="highlight">24+</span>
                    <span>
                        <span class="satisfied">satisfied</span>
                        <span class="customers">customers</span>
                    </span> -->
                </div>
            </div>
            <div class="hero-image">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300">
                    <rect width="400" height="300" fill="#1a1a1a" />

                    <!-- Background Grid -->
                    <g stroke="#333" stroke-width="0.5">
                        <line x1="0" y1="0" x2="400" y2="0" />
                        <line x1="0" y1="100" x2="400" y2="100" />
                        <line x1="0" y1="200" x2="400" y2="200" />
                        <line x1="0" y1="300" x2="400" y2="300" />
                        <line x1="0" y1="0" x2="0" y2="300" />
                        <line x1="133" y1="0" x2="133" y2="300" />
                        <line x1="266" y1="0" x2="266" y2="300" />
                        <line x1="400" y1="0" x2="400" y2="300" />
                    </g>

                    <!-- Chart Elements -->
                    <g fill="none" stroke-width="2">
                        <polyline points="20,280 80,200 140,240 200,120 260,180 320,60 380,100" stroke="#4CAF50" />
                        <polyline points="20,260 80,220 140,260 200,180 260,200 320,100 380,140" stroke="#2196F3" />
                    </g>

                    <!-- Circular Progress -->
                    <circle cx="200" cy="150" r="60" fill="none" stroke="#FFC107" stroke-width="10" stroke-dasharray="330 380" />

                    <!-- Data Points -->
                    <g fill="#fff">
                        <circle cx="80" cy="200" r="4" />
                        <circle cx="140" cy="240" r="4" />
                        <circle cx="200" cy="120" r="4" />
                        <circle cx="260" cy="180" r="4" />
                        <circle cx="320" cy="60" r="4" />
                    </g>

                    <!-- Text Elements -->
                    <g font-family="Arial, sans-serif" font-size="14" fill="#fff">
                        <text x="20" y="30">Dashboard</text>
                        <text x="300" y="30">Analytics</text>
                        <text x="180" y="150" text-anchor="middle">76%</text>
                    </g>
                </svg>
            </div>
            <div class="glow-effect" style="top: 20%; left: 10%;"></div>
            <div class="glow-effect" style="bottom: 10%; right: 20%;"></div>
        </section>

        <!-- Section 2: Features Section -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

        <section class="features">

        <div class="feature-cards">
    <div class="feature-card">
        <div class="feature-card-icon"><i class="fas fa-robot"></i></div>
        <h3>AI integration</h3>
        <p>Competitive edge by driving operational excellence, customer engagement, and fostering innovation.</p>
    </div>
    <div class="feature-card">
        <div class="feature-card-icon"><i class="fas fa-chart-line"></i></div>
        <h3>Dashboards</h3>
        <p>Get the insights that matter to your business at a glance with dashboards that adapt to your specific needs.</p>
    </div>
    <div class="feature-card">
        <div class="feature-card-icon"><i class="fas fa-briefcase"></i></div>
        <h3>Business Growth Solutions</h3>
        <p>Enhance growth with tools that improve productivity, accountability, and the overall efficiency of your organization.</p>
    </div>
    <div class="feature-card">
        <div class="feature-card-icon"><i class="fas fa-tools"></i></div>
        <h3>24/7 Technical Support</h3>
        <p>Access round-the-clock technical support to ensure your business applications run smoothly, minimizing downtime.</p>
    </div>
</div>


            <div class="feature-text">
                <h2>
                    Transform your business operations with our <span class="highlight-blue">tailored_solutions</span> designed to <span class="highlight-green">boost_efficiency</span> improve <span class="highlight-orange">accuracy</span> and ensure <span class="highlight-blue">accountability</span>
                </h2>
            </div>
        </section>

        <!-- Section 3: Image Slideshow -->
        <section class="slideshow">
            <img src="your-slideshow-image-url-here.jpg" alt="Slideshow Image">
        </section>
    </div>
</body>

</html>