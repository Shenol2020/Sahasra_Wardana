<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Home - Sri Lanka Help</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Lato', sans-serif;
            width: 1440px;
            height: 4350px;
            margin: 0 auto;
            position: relative;
            background: linear-gradient(180deg, rgba(44, 122, 63, 1) 0%, #E9EAD5 40%, rgba(243, 248, 244, 1) 100%);
            background-size: 100% 4350px;
            background-repeat: no-repeat; 
            overflow-x: hidden;
        }

        .logo {
            position: absolute;
            left: 55px;
            top: 30px;
            width: 230px;
            height: 84px;
        }

        .logo img {
            width: 100%;
            height: 100%;
            object-fit: fill;
        }

        .nav-menu {
            position: absolute;
            left: 412px;
            top: 162px;
            width: 616px;
            height: 14px;
        }

        .nav-menu a {
            position: absolute;
            font-family: 'Lato', sans-serif;
            font-weight: 400;
            font-size: 20px;
            line-height: 1.2em;
            color: #FFFEFD;
            text-decoration: none;
            text-align: left;
        }

        .nav-menu .nav-item:nth-child(1) {
            left: 0;
            top: 0;
            width: 60px;
            height: 14px;
        }

        .nav-menu .nav-item:nth-child(2) {
            left: 172px;
            top: 0;
            width: 63px;
            height: 14px;
        }

        .nav-menu .nav-item:nth-child(3) {
            left: 347px;
            top: 0;
            width: 75px;
            height: 14px;
        }

        .nav-menu .nav-item:nth-child(4) {
            left: 534px;
            top: 0;
            width: 82px;
            height: 14px;
        }

        .login-btn {
            position: absolute;
            left: 1237px;
            top: 47px;
            width: 148px;
            height: 55px;
            background: #D9D9D9;
            border-radius: 25px;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px 33px;
            cursor: pointer;
            text-decoration: none;
        }

        .login-btn span {
            font-family: 'Lato', sans-serif;
            font-weight: 600;
            font-size: 20px;
            line-height: 1.2em;
            color: #1E1E1E;
            text-align: left;
        }

        .hero-image {
            position: absolute;
            left: 707px;
            top: 312px;
            width: 720px;
            height: 389px;
            border-radius: 20px;
            overflow: hidden;
        }

        .hero-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .main-heading {
            position: absolute;
            left: 40px;
            top: 437px;
            width: 650px;
            height: 60px;
            font-family: 'Lato', sans-serif;
            font-weight: 600;
            font-size: 36px;
            line-height: 1.2em;
            color: #FFFFFF;
            text-align: left;
        }

        .subheading {
            position: absolute;
            left: 82px;
            top: 495px;
            width: 515px;
            height: 86px;
            font-family: 'Lato', sans-serif;
            font-weight: 600;
            font-size: 25px;
            line-height: 1.4em;
            color: #F1F1F1;
            text-align: left;
            white-space: pre-line;
        }

        .about-heading {
            position: absolute;
            left: 960px;
            top: 760px;
            width: 117px;
            height: 26px;
            font-family: 'Lato', sans-serif;
            font-weight: 600;
            font-size: 36px;
            line-height: 1.2em;
            color: #000000;
            text-align: left;
        }

        .about-image {
            position: absolute;
            left: 21px;
            top: 756px;
            width: 403px;
            height: 738px;
            border-radius: 10px;
            overflow: hidden;
        }

        .about-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .about-carousel {
            position: absolute;
            left: 670px;
            top: 825px;
            width: 700px;
            min-height: 300px;
            color: #000;
        }

        .about-viewport {
            position: relative;
            overflow-x: hidden;
            overflow-y: hidden;
            -webkit-overflow-scrolling: auto;
            width: 100%;
            min-height: 300px;
            max-height: 300px;
            border-radius: 10px;
            background: #E9EAD5;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
            padding: 26px 48px;
        }

        .about-track {
            display: flex;
            width: 100%;
            height: auto;
            align-items: flex-start;
            transform: translateX(0%);
            transition: transform 280ms ease;
            will-change: transform;
            gap: 0;
        }

        .about-card {
            flex: 0 0 100%;
            min-width: 100%;
            padding: 0 47px;
            font-family: 'Lato', sans-serif;
            font-weight: 600;
            font-size: 25px;
            line-height: 1.6;
            text-align: center;
            overflow-wrap: anywhere;
            word-break: break-word;
            white-space: normal;
        }

        .about-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 38px;
            height: 38px;
            border: none;
            border-radius: 50%;
            background: #2C7A3F;
            color: #fff;
            font-size: 22px;
            line-height: 1;
            cursor: pointer;
            display: grid;
            place-items: center;
            box-shadow: 0 6px 16px rgba(0,0,0,0.18);
            z-index: 2;
        }

        .about-arrow.left { left: 6px; }
        .about-arrow.right { right: 6px; }

        .about-arrow:disabled {
            opacity: 0.4;
            cursor: default;
            box-shadow: none;
        }

        .about-dots {
            margin-top: 12px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .about-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            border: none;
            background: #cfd8cf;
            cursor: pointer;
        }

        .about-dot[aria-selected="true"] {
            background: #2C7A3F;
        }

        .visions-image {
            position: absolute;
            left: 707px;
            top: 1300px;
            width: 720px;
            height: 344px;
            border-radius: 15px;
            overflow: hidden;
        }

        .visions-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .visions-section {
            position: absolute;
            left: 0;
            top: 1753px;
            width: 1440px;
            height: 1030px;
        }

        .vision {
            padding: 80px 20px;
            background: linear-gradient(135deg, #2C7A3F 0%, #3CA55C 100%);
            color: #FFFFFF;
        }
        .vision-content {
            max-width: 1000px;
            margin: 0 auto;
            text-align: center;
        }
        .vision h2 {
            font-family: 'Lato', sans-serif;
            font-weight: 600;
            font-size: 42px;
            line-height: 1.2em;
            margin-bottom: 30px;
        }
        .vision p {
            font-family: 'Lato', sans-serif;
            font-weight: 400;
            font-size: 20px;
            line-height: 1.8;
            margin-bottom: 25px;
            opacity: 0.95;
        }
        .vision-goals {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 50px;
            perspective: 1000px;
            perspective-origin: center;
        }
        .vision-goal {
            background: rgba(255, 255, 255, 0.12);
            padding: 30px;
            border-radius: 12px;
            backdrop-filter: blur(10px);
            position: relative;
            transform-style: preserve-3d;
            transform: rotateX(0) rotateY(0) translateZ(0);
            transition: transform 180ms ease, box-shadow 180ms ease, background 180ms ease;
            box-shadow: 0 12px 24px rgba(0,0,0,0.15), 0 2px 6px rgba(0,0,0,0.08);
            will-change: transform;
            isolation: isolate;
            overflow: hidden;
        }
        .vision-goal::before {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: linear-gradient(145deg, rgba(255,255,255,0.10), rgba(255,255,255,0.02));
            transform: translateZ(-30px) scale(0.98);
            z-index: -1;
        }
        .vision-goal::after {
            content: "";
            position: absolute;
            inset: -1px;
            border-radius: inherit;
            background: radial-gradient(500px circle at var(--mx, 50%) var(--my, 50%), rgba(255,255,255,0.22), transparent 40%);
            opacity: 0;
            transition: opacity 180ms ease;
            pointer-events: none;
            transform: translateZ(40px);
        }
        .vision-goal:hover {
            transform: translateY(-6px) scale(1.02);
            box-shadow: 0 18px 40px rgba(0,0,0,0.25), 0 6px 12px rgba(0,0,0,0.12);
        }
        .vision-goal:hover::after {
            opacity: 1;
        }
        .vision-goal h3,
        .vision-goal p {
            transform: translateZ(0);
            transition: transform 180ms ease;
        }

        @media (prefers-reduced-motion: reduce) {
            .vision-goal,
            .vision-goal h3,
            .vision-goal p { transition: none; }
        }

        .features {
            position: absolute;
            left: 0;
            top: 2783px;
            width: 1440px;
            height: 1020px;
            padding: 80px 20px;
            background: #f9f9f9;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .section-title {
            text-align: center;
            font-size: 36px;
            color: #333;
            margin-bottom: 20px;
            font-family: 'Lato', sans-serif;
            font-weight: 600;
        }
        .section-subtitle {
            text-align: center;
            font-size: 18px;
            color: #666;
            margin-bottom: 60px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.8;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            margin-top: 50px;
        }
        .feature-card {
            background: white;
            padding: 40px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
        }
        .feature-icon {
            font-size: 60px;
            margin-bottom: 20px;
        }
        .feature-card h3 {
            font-size: 24px;
            color: #333;
            margin-bottom: 15px;
        }
        .feature-card p {
            color: #666;
            line-height: 1.8;
        }

        .contact-heading {
            position: absolute;
            left: 648px;
            top: 3893px;
            width: 151px;
            height: 26px;
            font-family: 'Lato', sans-serif;
            font-weight: 600;
            font-size: 36px;
            line-height: 1.2em;
            color: #000000;
            text-align: left;
        }

        .contact-info {
            position: absolute;
            left: 153px;
            top: 3973px;
            width: 365px;
            height: 97px;
            font-family: 'Lato', sans-serif;
            font-weight: 300;
            font-size: 24px;
            line-height: 1.6666666666666667em;
            color: #000000;
            text-align: left;
            white-space: pre-line;
        }

        .social-icons {
            position: absolute;
            left: 928px;
            top: 4000px;
            width: 400px;
            height: 80px;
            display: flex;
            flex-direction: row;
            gap: 10px;
            padding: 10px;
            align-items: stretch;
        }

        .social-icon {
            position: absolute;
            width: 68px;
            height: 68px;
        }

        .social-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .social-icon:nth-child(1) {
            left: 30px;
            top: -11.5px;
        }

        .social-icon:nth-child(2) {
            left: 160px;
            top: -11.5px;
        }

        .social-icon:nth-child(3) {
            left: 290px;
            top: -11.5px;
        }

        .footer-image {
            position: absolute;
            left: 289px;
            top: 4124px;
            width: 862px;
            height: 220px;
        }

        .footer-image img {
            width: 100%;
            height: 100%;
            object-fit: fill;
        }
    </style>
</head>
<body>
    <div class="logo">
        <img src="assets/images/figma/logo-4292be.png" alt="Logo">
    </div>

    <nav class="nav-menu">
        <a href="#home" class="nav-item">Home</a>
        <a href="#about" class="nav-item">About</a>
        <a href="#visions" class="nav-item">Visions</a>
        <a href="#contact" class="nav-item">Contact</a>
    </nav>

    <a href="login.php" class="login-btn" style="text-decoration: none;">
        <span>Log in</span>
    </a>

    <div id="home" class="hero-image">
        <img src="assets/images/figma/hero_image.png" alt="Hero Image">
    </div>

    <h1 class="main-heading">Connecting Farmers Directly to Buyers </h1>

    <div class="subheading">A transparent marketplace where farmers sell fresh agricultural products at fair prices, and buyers get 
        quality produce directly from the source.</div>

    <h2 id="about" class="about-heading">About </h2>
    <div class="about-image">
        <img src="assets/images/figma/about_image.png" alt="About Image">
    </div>


    <div class="about-carousel" aria-label="About slides" role="region">
      <button class="about-arrow left" aria-label="Previous slide">‹</button>

      <div class="about-viewport">
        <div class="about-track">
          <article class="about-card">
            Sahasra Wardana is a revolutionary online platform designed to bridge the gap between farmers and buyers. We believe that farmers deserve fair compensation for their hard work, and buyers deserve access to fresh, quality produce at reasonable prices.
          </article>
          <article class="about-card">
            Our platform eliminates the traditional middleman system that often leaves farmers with minimal profits while buyers pay inflated prices. By creating a direct connection, we ensure transparency, fairness, and efficiency in the agricultural supply chain.
          </article>
          <article class="about-card">
            Whether you're a farmer looking to expand your market reach or a buyer seeking fresh produce at wholesale prices, Sahasra Wardana provides the tools and platform you need to succeed.
          </article>
          <article class="about-card">
            Our Mission: To empower farmers with technology, provide buyers with quality products, and create a sustainable agricultural ecosystem that benefits everyone.
          </article>
        </div>
      </div>

      <button class="about-arrow right" aria-label="Next slide">›</button>

      <div class="about-dots" role="tablist" aria-label="Slide selectors">
        <button class="about-dot" aria-label="Slide 1" aria-selected="true"></button>
        <button class="about-dot" aria-label="Slide 2"></button>
        <button class="about-dot" aria-label="Slide 3"></button>
        <button class="about-dot" aria-label="Slide 4"></button>
      </div>
    </div>

    <div class="visions-image">
        <img src="assets/images/figma/visions_image.png" alt="Visions Image">
    </div>

    <div id="visions" class="visions-section vision">
        <div class="vision-content">
            <h2>Our Vision for the Future</h2>
            <p>We envision a future where every farmer has direct access to markets, every buyer gets fresh produce at fair prices, and the agricultural supply chain is transparent, efficient, and sustainable.</p>
            <p>Sahasra Wardana is more than just a marketplace – it's a movement towards agricultural empowerment, food security, and economic fairness for rural communities.</p>

            <div class="vision-goals">
                <div class="vision-goal">
                    <h3>🌍 Global Reach</h3>
                    <p>Expanding to connect farmers and buyers across regions and countries, creating a worldwide agricultural network.</p>
                </div>
                <div class="vision-goal">
                    <h3>🤖 Smart Technology</h3>
                    <p>Leveraging AI and data analytics to predict market trends, optimize pricing, and improve supply chain efficiency.</p>
                </div>
                <div class="vision-goal">
                    <h3>🌱 Sustainability</h3>
                    <p>Promoting organic farming, reducing food waste, and supporting environmentally friendly agricultural practices.</p>
                </div>
                <div class="vision-goal">
                    <h3>👥 Community Building</h3>
                    <p>Creating a strong community of farmers and buyers who support each other and share knowledge.</p>
                </div>
            </div>
        </div>
    </div>

    <section class="features">
        <div class="container">
            <h2 class="section-title">Why Choose Sahasra Wardana?</h2>
            <p class="section-subtitle">We're revolutionizing the agricultural marketplace by eliminating middlemen and creating direct connections</p>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🚜</div>
                    <h3>Direct Trade</h3>
                    <p>Farmers sell directly to buyers without middlemen, ensuring better prices for both parties.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💰</div>
                    <h3>Fair Pricing</h3>
                    <p>Transparent wholesale prices with real-time market data and price trend analysis.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Market Insights</h3>
                    <p>Access price statistics and trends to make informed decisions about buying and selling.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🌾</div>
                    <h3>Fresh Products</h3>
                    <p>Get fresh vegetables, fruits, and grains directly from local farmers in your region.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💬</div>
                    <h3>Direct Communication</h3>
                    <p>Message farmers directly to discuss products, quantities, and delivery arrangements.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📱</div>
                    <h3>Easy to Use</h3>
                    <p>Simple, mobile-friendly platform designed for farmers and buyers of all technical levels.</p>
                </div>
            </div>
        </div>
    </section>

    <h2 id="contact" class="contact-heading">Contact </h2>
    <div class="contact-info">Shenoldisanayaka@gmail.com
                                +94719223844
                            hettipola,matale </div>
    <div class="social-icons">
        <div class="social-icon">
            <img src="assets/images/figma/social_icon_1.png" alt="Social Icon 1">
        </div>
        <div class="social-icon">
            <img src="assets/images/figma/social_icon_2.png" alt="Social Icon 2">
        </div>
        <div class="social-icon">
            <img src="assets/images/figma/social_icon_3.png" alt="Social Icon 3">
        </div>
    </div>

    <div class="footer-image">
        <img src="assets/images/figma/footer_image-23040b.png" alt="Footer Image">
    </div>

    <script>
        (function () {
            const cards = document.querySelectorAll('.vision-goal');
            const clamp = (v, min, max) => Math.max(min, Math.min(max, v));

            cards.forEach(card => {
                const update = (point) => {
                    const rect = card.getBoundingClientRect();
                    const x = point.clientX - rect.left;
                    const y = point.clientY - rect.top;
                    const midX = rect.width / 2;
                    const midY = rect.height / 2;

                    const rotY = clamp(((x - midX) / midX) * 12, -15, 15);
                    const rotX = clamp((-(y - midY) / midY) * 12, -15, 15);

                    card.style.setProperty('--mx', x + 'px');
                    card.style.setProperty('--my', y + 'px');
                    card.style.transform = `rotateX(${rotX}deg) rotateY(${rotY}deg) translateZ(0)`;

                    const h3 = card.querySelector('h3');
                    const p = card.querySelector('p');
                    if (h3) h3.style.transform = 'translateZ(35px)';
                    if (p) p.style.transform = 'translateZ(20px)';
                };

                const clear = () => {
                    card.style.transform = 'rotateX(0) rotateY(0) translateZ(0)';
                    const h3 = card.querySelector('h3');
                    const p = card.querySelector('p');
                    if (h3) h3.style.transform = 'translateZ(0)';
                    if (p) p.style.transform = 'translateZ(0)';
                };

                card.addEventListener('mousemove', update);
                card.addEventListener('mouseleave', clear);

                card.addEventListener('touchmove', (e) => {
                    if (!e.touches || !e.touches[0]) return;
                    const t = e.touches[0];
                    update({ clientX: t.clientX, clientY: t.clientY });
                }, { passive: true });

                card.addEventListener('touchend', clear);
            });
        })();
    </script>

    <script>
  (function () {
    const root = document.querySelector('.about-carousel');
    if (!root) return;

    const track = root.querySelector('.about-track');
    const cards = Array.from(root.querySelectorAll('.about-card'));
    const prevBtn = root.querySelector('.about-arrow.left');
    const nextBtn = root.querySelector('.about-arrow.right');
    const dots = Array.from(root.querySelectorAll('.about-dot'));

    let index = 0;
    const total = cards.length;

    function update() {
      track.style.transform = 'translateX(' + (-index * 100) + '%)';
      prevBtn.disabled = index === 0;
      nextBtn.disabled = index === total - 1;
      dots.forEach((d, i) => d.setAttribute('aria-selected', i === index ? 'true' : 'false'));
    }

    prevBtn.addEventListener('click', () => { if (index > 0) { index--; update(); } });
    nextBtn.addEventListener('click', () => { if (index < total - 1) { index++; update(); } });
    dots.forEach((d, i) => d.addEventListener('click', () => { index = i; update(); }));

    root.tabIndex = 0;
    root.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowLeft') { if (index > 0) { index--; update(); } }
      if (e.key === 'ArrowRight') { if (index < total - 1) { index++; update(); } }
    });

    update();
  })();
</script>
</body>
</html>
