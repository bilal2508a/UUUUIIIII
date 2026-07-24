<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!$name || !$email || !$message) {
        flash('error', 'Please fill in all required fields.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Please enter a valid email address.');
    } else {
        try {
            $stmt = db()->prepare('INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $email, $subject, $message]);
            flash('success', 'Message sent! We will get back to you soon.');
        } catch (Exception $e) {
            flash('error', 'Failed to send message. Please try again.');
        }
    }
    redirect('/contact.php');
}

include __DIR__ . '/includes/header.php';
?>

<div style="background:linear-gradient(135deg,#0ea5e9,#14b8a6);padding:3rem 0;color:#fff;">
    <div class="container-app">
        <h1 style="font-size:2.25rem;font-weight:800;margin:0;letter-spacing:-0.02em;">Contact Us</h1>
        <p style="margin:0.5rem 0 0;opacity:0.95;font-size:1.05rem;">Have questions? We're here to help</p>
    </div>
</div>

<section style="padding:3rem 0;">
    <div class="container-app">
        <div class="row g-4">
            <!-- Contact Info -->
            <div class="col-lg-4">
                <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:2rem;height:100%;">
                    <h4 style="color:#0f172a;font-weight:700;margin:0 0 1rem;"><i class="bi bi-chat-dots" style="color:#0ea5e9;"></i> Get in Touch</h4>
                    <p style="color:#64748b;line-height:1.6;">Whether you're looking for a rental or want to list your property, we're here to help.</p>

                    <div style="margin-top:1.5rem;">
                        <div style="display:flex;align-items:flex-start;gap:1rem;padding:0.75rem 0;">
                            <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#0ea5e9,#0ea5e9);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;">
                                <i class="bi bi-geo-alt-fill"></i>
                            </div>
                            <div>
                                <strong style="color:#0f172a;">Address</strong>
                                <p style="color:#64748b;margin:0.25rem 0 0;font-size:0.9rem;">Main Boulevard, Lahore, Pakistan</p>
                            </div>
                        </div>
                        <div style="display:flex;align-items:flex-start;gap:1rem;padding:0.75rem 0;">
                            <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#14b8a6,#14b8a6);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;">
                                <i class="bi bi-telephone-fill"></i>
                            </div>
                            <div>
                                <strong style="color:#0f172a;">Phone</strong>
                                <p style="color:#64748b;margin:0.25rem 0 0;font-size:0.9rem;">+92 300 1234567</p>
                            </div>
                        </div>
                        <div style="display:flex;align-items:flex-start;gap:1rem;padding:0.75rem 0;">
                            <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#0ea5e9,#14b8a6);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;">
                                <i class="bi bi-envelope-fill"></i>
                            </div>
                            <div>
                                <strong style="color:#0f172a;">Email</strong>
                                <p style="color:#64748b;margin:0.25rem 0 0;font-size:0.9rem;">info@mehmaanhub.com</p>
                            </div>
                        </div>
                        <div style="display:flex;align-items:flex-start;gap:1rem;padding:0.75rem 0;">
                            <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#14b8a6,#0ea5e9);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;">
                                <i class="bi bi-clock-fill"></i>
                            </div>
                            <div>
                                <strong style="color:#0f172a;">Hours</strong>
                                <p style="color:#64748b;margin:0.25rem 0 0;font-size:0.9rem;">Monday - Saturday: 9AM - 7PM</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-lg-8">
                <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:2.5rem;">
                    <h4 style="color:#0f172a;font-weight:700;margin:0 0 1.5rem;"><i class="bi bi-send" style="color:#14b8a6;"></i> Send a Message</h4>
                    <form method="POST" action="<?php echo url('/contact.php'); ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label style="font-size:0.85rem;font-weight:600;color:#64748b;margin-bottom:0.25rem;display:block;">Name <span style="color:#ef4444;">*</span></label>
                                <input type="text" name="name" class="form-control" style="border-radius:10px;" required>
                            </div>
                            <div class="col-md-6">
                                <label style="font-size:0.85rem;font-weight:600;color:#64748b;margin-bottom:0.25rem;display:block;">Email <span style="color:#ef4444;">*</span></label>
                                <input type="email" name="email" class="form-control" style="border-radius:10px;" required>
                            </div>
                            <div class="col-12">
                                <label style="font-size:0.85rem;font-weight:600;color:#64748b;margin-bottom:0.25rem;display:block;">Subject</label>
                                <input type="text" name="subject" class="form-control" style="border-radius:10px;">
                            </div>
                            <div class="col-12">
                                <label style="font-size:0.85rem;font-weight:600;color:#64748b;margin-bottom:0.25rem;display:block;">Message <span style="color:#ef4444;">*</span></label>
                                <textarea name="message" rows="6" class="form-control" style="border-radius:10px;" required></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" style="margin-top:1.5rem;border-radius:10px;padding:0.75rem 2rem;"><i class="bi bi-send"></i> Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
