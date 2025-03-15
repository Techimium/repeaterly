<style>
    .repeater-overview {
        margin-left: -20px;
        padding: 20px;
        background: #f5f6f9;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        border-radius: 4px;
    }

    .repeater-header {
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 20px;
        text-align: center;
        padding: 40px;
        min-height: 300px;
        margin: auto;
        background: linear-gradient(45deg, #f3e5f5, #e1bee7, #bbdefb, #b2ebf2, #c8e6c9, #fff9c4);
        color: #fff;
        border-radius: 4px;
        margin-bottom: 40px;
        box-shadow: 0px 2px 30px #eaeaea;
    }

    .repeater-header h1 {
        font-size: 4.5em;
        font-weight: bold;
        line-height: 1em;
        margin: 0;
    }

    .repeater-header p {
        font-size: 1.2em;
        margin: 10px 0 0;
        color: #111;
    }

    .repeater-features {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }

    .feature-card {
        background: #fff;
        padding: 20px;
        border-radius: 4px;
        border: 1px solid #e5e5e5;
        text-align: center;
        box-shadow: 0px 2px 30px #eaeaea;
    }

    .feature-card h3 {
        font-size: 1.3em;
        margin: 0 0 10px;
        color: #2575fc;
    }

    .feature-card p {
        font-size: 0.95em;
        margin: 0;
        color: #666;
    }

    .repeater-cta {
        text-align: center;
        padding: 40px 20px;
        background: #fff;
        border-radius: 4px;
        box-shadow: 0px 2px 30px #eaeaea;
    }

    .repeater-cta h2 {
        font-size: 2em;
        margin: 0 0 20px;
    }

    .repeater-cta p {
        font-size: 1.1em;
        margin: 0 0 30px;
        color: #666;
    }

    .repeater-cta .cta-button {
        display: inline-block;
        padding: 15px 30px;
        font-size: 1.1em;
        color: #fff;
        background: #2575fc;
        border-radius: 4px;
        text-decoration: none;
        transition: background 0.3s ease;
    }

    .repeater-cta .cta-button:hover {
        background: #1a5bbf;
    }

    .repeater-footer {
        text-align: center;
        padding: 20px;
        font-size: 0.9em;
        color: #666;
        border-top: 1px solid #e5e5e5;
        margin-top: 40px;
    }
</style>
<div class="repeater-overview">
    <!-- Header Section -->
    <div class="repeater-header">
        <h1>Welcome to Repeaterly</h1>
        <p>Unlock ACF Repeater Fields, Gallery Fields, ACF Repeater Loop Builder and Dynamic Content for Elementor</p>
    </div>

    <!-- Features Section -->
    <div class="repeater-features">
        <div class="feature-card">
            <h3>🔁 ACF Repeater Loop Builder (Pro)</h3>
            <p>Unlock the full potential of ACF Repeater Fields by dynamically displaying custom layouts using Elementor. Create stunning grids, lists, and carousels with ease.</p>
        </div>
        <div class="feature-card">
    <h3>📋 ACF Repeater List Widget</h3>
    <p>Perfect for simple dynamic lists! Display repeater field data with a single text field—ideal for bullet points, feature lists, FAQs, and more, without any coding.</p>
</div>
        <div class="feature-card">
            <h3>🖼️ ACF Gallery Widget</h3>
            <p>Design fully customizable image galleries using ACF Gallery Fields. Display beautiful, responsive image collections inside Elementor with total creative control.</p>
        </div>
        <div class="feature-card">
            <h3>🎠 ACF Image Carousel Widget (Pro)</h3>
            <p>Turn your repeater field images into a sleek, interactive carousel. Enjoy full design flexibility while keeping your site lightweight and fast.</p>
        </div>
        <div class="feature-card">
            <h3>⚡ Works Without Elementor Pro</h3>
            <p>Enjoy advanced dynamic content capabilities without needing Elementor Pro. Repeaterly expands Elementor’s functionality for free users.</p>
        </div>
        <div class="feature-card">
            <h3>🚀 Performance Optimized</h3>
            <p>Designed for speed, Repeaterly helps reduce database bloat by reusing layouts instead of duplicating sections, ensuring your website runs smoothly.</p>
        </div>
    </div>


    <!-- Call-to-Action Section -->
    <div class="repeater-cta">
        <h2>Need More? Try Our Pro Version</h2>
        <p>Unlock even more powerful features like custom ACF Repeater Loop Builder.</p>
        <a href="https://techimium.com/repeaterly-pro" class="cta-button">Get Repeater Pro Now</a>
    </div>
</div>