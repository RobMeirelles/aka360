<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $message = sanitizeInput($_POST['message']);
    $course_id = isset($_POST['course_id']) ? (int)$_POST['course_id'] : null;
    
    // Basic validation
    if (empty($name) || empty($email) || empty($message)) {
        $error_message = 'Por favor completa todos los campos obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Por favor ingresa un email válido.';
    } else {
        // Save to database
        $data = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'message' => $message,
            'course_id' => $course_id
        ];
        
        if (saveContactSubmission($data)) {
            // Send email notification to administrators
            $subject = 'Nueva consulta desde Akademia 360';
            $email_message = "
                <h2>Nueva consulta recibida</h2>
                <p><strong>Nombre:</strong> {$name}</p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Teléfono:</strong> {$phone}</p>
                <p><strong>Mensaje:</strong></p>
                <p>{$message}</p>
            ";
            
            if (sendEmailNotification('contacto@akademia360.cl', $subject, $email_message)) {
                $success_message = '¡Gracias por tu mensaje! Te contactaremos pronto.';
            } else {
                $success_message = '¡Gracias por tu mensaje! Te contactaremos pronto.';
            }
        } else {
            $error_message = 'Error al enviar el mensaje. Por favor intenta nuevamente.';
        }
    }
}

// Get course info if course_id is provided
$course = null;
if (isset($_GET['curso'])) {
    $course = getCourseById($_GET['curso']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - Akademia 360</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <div class="logo">
                    <img src="../img/logo-akademia360.png" alt="Akademia 360" onerror="this.style.display='none'">
                </div>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">INICIO</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="nosotros.php">NOSOTROS</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="calendario_cursos.php">CURSOS</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="relatores.php">RELATORES</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="noticias.php">NOTICIAS</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="contacto.php">CONTACTO</a>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main style="margin-top: 80px;">
        <!-- Contact Hero Section -->
        <section class="contact-hero py-5" style="background: var(--gradient-primary);">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8 text-center text-white">
                        <h1 class="display-4 fw-bold mb-4">Contáctanos</h1>
                        <p class="lead">¿Tienes alguna pregunta o quieres saber más sobre nuestros servicios? 
                        Estamos aquí para ayudarte.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Form Section -->
        <section class="contact-section py-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white">
                                <h4 class="mb-0"><i class="fas fa-envelope"></i> Envíanos un mensaje</h4>
                            </div>
                            <div class="card-body p-4">
                                <?php if ($success_message): ?>
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($error_message): ?>
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                                    </div>
                                <?php endif; ?>

                                <form method="POST" id="contactForm">
                                    <?php if ($course): ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i> 
                                            Consulta sobre: <strong><?php echo htmlspecialchars($course['title']); ?></strong>
                                        </div>
                                        <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                    <?php endif; ?>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Nombre completo *</label>
                                                <input type="text" class="form-control" id="name" name="name" 
                                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email *</label>
                                                <input type="email" class="form-control" id="email" name="email" 
                                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Teléfono</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label for="message" class="form-label">Mensaje *</label>
                                        <textarea class="form-control" id="message" name="message" rows="5" 
                                                  placeholder="Cuéntanos sobre tu consulta, interés en nuestros servicios o cualquier pregunta que tengas..." required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-paper-plane"></i> Enviar mensaje
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card shadow">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Información de contacto</h5>
                            </div>
                            <div class="card-body">
                                <div class="contact-info">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="contact-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                             style="width: 40px; height: 40px;">
                                            <i class="fas fa-envelope"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Email</h6>
                                            <p class="mb-0 text-muted">contacto@akademia360.cl</p>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center mb-3">
                                        <div class="contact-icon bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                             style="width: 40px; height: 40px;">
                                            <i class="fas fa-globe"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Sitio web</h6>
                                            <p class="mb-0 text-muted">www.akademia360.cl</p>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center mb-3">
                                        <div class="contact-icon bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                             style="width: 40px; height: 40px;">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Horario de atención</h6>
                                            <p class="mb-0 text-muted">Lunes a Viernes: 9:00 - 18:00</p>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center">
                                        <div class="contact-icon bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                             style="width: 40px; height: 40px;">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">Ubicación</h6>
                                            <p class="mb-0 text-muted">Chile</p>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <h6 class="mb-3">Síguenos en redes sociales</h6>
                                <div class="social-links">
                                    <a href="#" class="social-link me-2" title="Facebook">
                                        <i class="fab fa-facebook"></i>
                                    </a>
                                    <a href="#" class="social-link me-2" title="Instagram">
                                        <i class="fab fa-instagram"></i>
                                    </a>
                                    <a href="#" class="social-link me-2" title="TikTok">
                                        <i class="fab fa-tiktok"></i>
                                    </a>
                                    <a href="#" class="social-link" title="LinkedIn">
                                        <i class="fab fa-linkedin"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <?php if ($course): ?>
                        <div class="card shadow mt-4">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-graduation-cap"></i> Curso de interés</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <?php if ($course['image']): ?>
                                        <img src="<?php echo htmlspecialchars($course['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($course['title']); ?>" 
                                             class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                    <?php endif; ?>
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($course['title']); ?></h6>
                                        <p class="text-muted mb-0"><?php echo htmlspecialchars($course['short_description']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="faq-section py-5 bg-light">
            <div class="container">
                <h2 class="section-title text-center mb-5">Preguntas Frecuentes</h2>
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="accordion" id="faqAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                        ¿Cómo puedo inscribirme en un curso?
                                    </button>
                                </h2>
                                <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Puedes inscribirte en nuestros cursos a través de nuestro formulario de contacto, 
                                        especificando el curso de tu interés. También puedes contactarnos directamente 
                                        por email o teléfono.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                        ¿Ofrecen cursos online?
                                    </button>
                                </h2>
                                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Sí, ofrecemos tanto cursos presenciales como online. Nuestros cursos están 
                                        diseñados para adaptarse a diferentes modalidades de aprendizaje.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                        ¿Cuáles son los métodos de pago?
                                    </button>
                                </h2>
                                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Aceptamos transferencias bancarias, pagos con tarjeta de crédito/débito 
                                        y otros métodos de pago. Los detalles específicos se proporcionan al momento de la inscripción.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                        ¿Ofrecen certificados al finalizar los cursos?
                                    </button>
                                </h2>
                                <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Sí, todos nuestros cursos incluyen un certificado de participación 
                                        que se entrega al finalizar exitosamente el programa.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    <div class="footer-logo">
                        <div class="logo">
                            <img src="../img/logo-akademia360.png" alt="Akademia 360" style="height: 30px; width: auto;">
                        </div>
                    </div>
                    <p class="mt-3">Transforma tu forma de liderar y comunicar. Únete a nuestra comunidad de profesionales.</p>
                </div>
                <div class="col-lg-4">
                    <h5>Contacto</h5>
                    <p><i class="fas fa-envelope"></i> contacto@akademia360.cl</p>
                    <p><i class="fas fa-globe"></i> www.akademia360.cl</p>
                </div>
                <div class="col-lg-4">
                    <h5>Síguenos</h5>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p>&copy; 2024 Akademia 360. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../js/main.js"></script>
</body>
</html> 