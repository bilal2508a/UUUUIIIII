<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

include __DIR__ . '/includes/header.php';
?>

<div style="background:linear-gradient(135deg,#0ea5e9,#14b8a6);padding:4rem 0;color:#fff;position:relative;overflow:hidden;">
    <div style="position:absolute;inset:0;background-image:url('https://images.pexels.com/photos/323780/pexels-photo-323780.jpeg?auto=compress&cs=tinysrgb&w=1600');background-size:cover;background-position:center;opacity:0.2;mix-blend-mode:overlay;"></div>
    <div class="container-app" style="position:relative;z-index:2;text-align:center;">
        <h1 style="font-size:2.5rem;font-weight:800;margin:0 0 0.5rem;letter-spacing:-0.02em;">About Mehmaan Hub</h1>
        <p style="font-size:1.15rem;opacity:0.95;margin:0;">Your trusted rental property platform in Pakistan</p>
    </div>
</div>

<!-- Our Story -->
<section style="padding:4rem 0;">
    <div class="container-app">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <h2 style="font-size:2rem;font-weight:800;color:#0f172a;letter-spacing:-0.02em;">Our Story</h2>
                <p style="color:#64748b;line-height:1.7;margin-top:1rem;">Mehmaan Hub was created to simplify the rental property search in Pakistan. We connect property owners with potential tenants through a simple, transparent, and verified platform.</p>
                <p style="color:#64748b;line-height:1.7;">Whether you're looking for an apartment in the city, a house in the suburbs, or a room for short-term stay, Mehmaan Hub has you covered. Our platform offers verified listings, direct owner contact, and a seamless booking process.</p>
                <div style="display:flex;gap:1.5rem;margin-top:2rem;">
                    <div>
                        <div style="font-size:2rem;font-weight:800;color:#0ea5e9;">500+</div>
                        <small style="color:#64748b;">Happy Tenants</small>
                    </div>
                    <div>
                        <div style="font-size:2rem;font-weight:800;color:#14b8a6;">200+</div>
                        <small style="color:#64748b;">Property Owners</small>
                    </div>
                    <div>
                        <div style="font-size:2rem;font-weight:800;color:#0ea5e9;">15+</div>
                        <small style="color:#64748b;">Cities Covered</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="https://images.pexels.com/photos/323780/pexels-photo-323780.jpeg?auto=compress&cs=tinysrgb&w=800" alt="About Mehmaan Hub" style="width:100%;border-radius:20px;box-shadow:0 10px 40px rgba(0,0,0,0.1);">
            </div>
        </div>
    </div>
</section>

<!-- Mission -->
<section style="padding:4rem 0;background:#f8fafc;">
    <div class="container-app">
        <div class="card" style="border:none;border-radius:24px;padding:3rem;background:linear-gradient(135deg,#0ea5e9,#14b8a6);color:#fff;text-align:center;">
            <div style="width:72px;height:72px;border-radius:18px;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;color:#fff;font-size:2rem;margin:0 auto 1.5rem;">
                <i class="bi bi-bullseye"></i>
            </div>
            <h2 style="font-size:2rem;font-weight:800;margin:0 0 1rem;">Our Mission</h2>
            <p style="font-size:1.1rem;opacity:0.95;max-width:640px;margin:0 auto;line-height:1.7;">To make finding and listing rental properties in Pakistan effortless, transparent, and trustworthy. We eliminate middlemen, reduce hidden costs, and empower both tenants and owners with a seamless digital experience.</p>
        </div>
    </div>
</section>

<!-- Features -->
<section style="padding:4rem 0;">
    <div class="container-app">
        <div style="text-align:center;margin-bottom:3rem;">
            <h2 style="font-size:2rem;font-weight:800;color:#0f172a;letter-spacing:-0.02em;">Why Choose Us</h2>
            <p style="color:#64748b;margin-top:0.5rem;">Features that make us Pakistan's premier rental platform</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:2rem;height:100%;text-align:center;">
                    <div style="width:64px;height:64px;border-radius:16px;background:linear-gradient(135deg,#0ea5e9,#0ea5e9);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.5rem;margin:0 auto 1.25rem;">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h4 style="color:#0f172a;font-weight:700;">Verified Listings</h4>
                    <p style="color:#64748b;margin-top:0.5rem;">All properties on our platform are verified to ensure authenticity and quality.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:2rem;height:100%;text-align:center;">
                    <div style="width:64px;height:64px;border-radius:16px;background:linear-gradient(135deg,#14b8a6,#14b8a6);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.5rem;margin:0 auto 1.25rem;">
                        <i class="bi bi-people"></i>
                    </div>
                    <h4 style="color:#0f172a;font-weight:700;">Direct Owner Contact</h4>
                    <p style="color:#64748b;margin-top:0.5rem;">Connect directly with property owners without any middlemen or agents.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:2rem;height:100%;text-align:center;">
                    <div style="width:64px;height:64px;border-radius:16px;background:linear-gradient(135deg,#0ea5e9,#14b8a6);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.5rem;margin:0 auto 1.25rem;">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <h4 style="color:#0f172a;font-weight:700;">No Hidden Charges</h4>
                    <p style="color:#64748b;margin-top:0.5rem;">Transparent pricing with no hidden fees. What you see is what you pay.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:2rem;height:100%;text-align:center;">
                    <div style="width:64px;height:64px;border-radius:16px;background:linear-gradient(135deg,#14b8a6,#0ea5e9);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.5rem;margin:0 auto 1.25rem;">
                        <i class="bi bi-headset"></i>
                    </div>
                    <h4 style="color:#0f172a;font-weight:700;">24/7 Support</h4>
                    <p style="color:#64748b;margin-top:0.5rem;">Our support team is always available to help you with any questions.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team -->
<section style="padding:4rem 0;background:#f8fafc;">
    <div class="container-app">
        <div style="text-align:center;margin-bottom:3rem;">
            <h2 style="font-size:2rem;font-weight:800;color:#0f172a;letter-spacing:-0.02em;">Our Team</h2>
            <p style="color:#64748b;margin-top:0.5rem;">Meet the people behind Mehmaan Hub</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:2rem;text-align:center;">
                    <div style="width:96px;height:96px;border-radius:50%;background:linear-gradient(135deg,#0ea5e9,#14b8a6);color:#fff;display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:800;margin:0 auto 1rem;">A</div>
                    <h5 style="color:#0f172a;font-weight:700;margin:0;">Ahmed Khan</h5>
                    <small style="color:#0ea5e9;font-weight:600;">CEO &amp; Founder</small>
                    <p style="color:#64748b;font-size:0.85rem;margin-top:0.5rem;">Visionary leader with 10+ years in real estate.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:2rem;text-align:center;">
                    <div style="width:96px;height:96px;border-radius:50%;background:linear-gradient(135deg,#14b8a6,#0ea5e9);color:#fff;display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:800;margin:0 auto 1rem;">F</div>
                    <h5 style="color:#0f172a;font-weight:700;margin:0;">Fatima Sheikh</h5>
                    <small style="color:#0ea5e9;font-weight:600;">CTO</small>
                    <p style="color:#64748b;font-size:0.85rem;margin-top:0.5rem;">Tech expert leading our platform development.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:2rem;text-align:center;">
                    <div style="width:96px;height:96px;border-radius:50%;background:linear-gradient(135deg,#0ea5e9,#0ea5e9);color:#fff;display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:800;margin:0 auto 1rem;">B</div>
                    <h5 style="color:#0f172a;font-weight:700;margin:0;">Bilal Raza</h5>
                    <small style="color:#0ea5e9;font-weight:600;">Head of Operations</small>
                    <p style="color:#64748b;font-size:0.85rem;margin-top:0.5rem;">Ensures smooth operations and quality service.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:2rem;text-align:center;">
                    <div style="width:96px;height:96px;border-radius:50%;background:linear-gradient(135deg,#14b8a6,#14b8a6);color:#fff;display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:800;margin:0 auto 1rem;">S</div>
                    <h5 style="color:#0f172a;font-weight:700;margin:0;">Sara Ali</h5>
                    <small style="color:#0ea5e9;font-weight:600;">Customer Success</small>
                    <p style="color:#64748b;font-size:0.85rem;margin-top:0.5rem;">Dedicated to making your experience perfect.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
