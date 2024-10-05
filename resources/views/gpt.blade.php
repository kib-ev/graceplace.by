<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beauty Coworking Landing Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Шапка (Hero Section) -->
<header class="bg-dark text-white text-center py-5">
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">Grace Place - бьюти коворкинг</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="#about">About</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#services">Services</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#benefits">Benefits</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#testimonials">Testimonials</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#pricing">Pricing</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#faq">FAQ</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#contact">Contact</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <h1 class="mt-5">Beauty Coworking</h1>
        <p class="lead">Modern coworking space for beauty professionals</p>
        <a href="{{ url('/schedule') }}" class="btn btn-primary">Book a Spot</a>
    </div>
</header>

<!-- Добавьте этот скрипт для плавного перехода -->
<script>
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();

            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
</script>


<!-- О коворкинге -->
<section id="about" class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <h2>О нашем пространстве</h2>

                <p><b>Grace Place</b> — это удобное пространство для мастеров индустрии красоты, расположенное рядом с метро Петровщина. Мы предлагаем функциональные рабочие места для парикмахеров, мастеров маникюра, косметологов и массажистов. Работайте в комфортных условиях и планируйте свой график с возможностью доступа 24/7.</p>


{{--                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>--}}
{{--                <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>--}}
            </div>
            <div class="col-lg-6">
                <div id="aboutSlider" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <img src="https://via.placeholder.com/600x400" class="d-block w-100" alt="Coworking Space 1">
                        </div>
                        <div class="carousel-item">
                            <img src="https://via.placeholder.com/600x400" class="d-block w-100" alt="Coworking Space 2">
                        </div>
                        <div class="carousel-item">
                            <img src="https://via.placeholder.com/600x400" class="d-block w-100" alt="Coworking Space 3">
                        </div>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#aboutSlider" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#aboutSlider" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- Услуги -->
<section id="services" class="bg-light py-5">
    <div class="container">
        <h2 class="text-center mb-5">Наши рабочие места</h2>
        <div class="row">
            <!-- Парикмахерские места -->
            <div class="col-md-4 mb-4">
                <h3>Hairdresser Spots</h3>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                <img src="https://via.placeholder.com/300x200" class="img-fluid" alt="Hairdresser Spots">
            </div>
            <!-- Места для маникюра -->
            <div class="col-md-4 mb-4">
                <h3>Manicure Stations</h3>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                <img src="https://via.placeholder.com/300x200" class="img-fluid" alt="Manicure Stations">
            </div>
            <!-- Отдельные кабинеты -->
            <div class="col-md-4 mb-4">
                <h3>Private Rooms</h3>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                <img src="https://via.placeholder.com/300x200" class="img-fluid" alt="Hairdressing Room">
            </div>
            <div class="col-md-4 mb-4">
                <h3>Manicure Stations</h3>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                <img src="https://via.placeholder.com/300x200" class="img-fluid" alt="Cosmetology Room">
            </div>
            <div class="col-md-4 mb-4">
                <h3>Manicure Stations</h3>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                <img src="https://via.placeholder.com/300x200" class="img-fluid" alt="Massage Room">
            </div>
        </div>
    </div>
</section>


<!-- Преимущества -->
<section id="benefits" class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Why Choose Us</h2>
        <div class="row">
            <div class="col-md-3">
                <h4>Flexible Rentals</h4>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
            </div>
            <div class="col-md-3">
                <h4>Modern Equipment</h4>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
            </div>
            <div class="col-md-3">
                <h4>Convenient Location</h4>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
            </div>
            <div class="col-md-3">
                <h4>Client-Oriented</h4>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
            </div>
        </div>
    </div>
</section>

<!-- Отзывы -->
<section id="testimonials" class="bg-light py-5">
    <div class="container">
        <h2 class="text-center mb-5">Client Testimonials</h2>
        <div class="row">
            <div class="col-md-4">
                <blockquote class="blockquote">
                    <p class="mb-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer posuere erat a ante.</p>
                    <footer class="blockquote-footer">Jane Doe, <cite title="Source Title">Hairdresser</cite></footer>
                </blockquote>
            </div>
            <div class="col-md-4">
                <blockquote class="blockquote">
                    <p class="mb-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer posuere erat a ante.</p>
                    <footer class="blockquote-footer">John Smith, <cite title="Source Title">Manicurist</cite></footer>
                </blockquote>
            </div>
            <div class="col-md-4">
                <blockquote class="blockquote">
                    <p class="mb-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer posuere erat a ante.</p>
                    <footer class="blockquote-footer">Emily Johnson, <cite title="Source Title">Cosmetologist</cite></footer>
                </blockquote>
            </div>
        </div>
    </div>
</section>

<!-- Тарифы и доступные места -->
<section id="pricing" class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Pricing and Availability</h2>
        <div class="row">
            <div class="col-md-6">
                <h4>Hourly Rental</h4>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
            </div>
            <div class="col-md-6">
                <h4>Daily Rental</h4>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="#booking" class="btn btn-primary">Check Availability</a>
        </div>
    </div>
</section>


<!-- Блок выбора рабочего места, дня и времени -->
<section id="booking" class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Book Your Workspace</h2>
        <div class="row">
            <!-- Шаг 1: Выбор рабочего места -->
            <div class="col-md-4">
                <h4>Choose Your Workspace</h4>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Hairdresser Spot #1</h5>
                        <p class="card-text">A well-lit station with all necessary tools.</p>
                        <button class="btn btn-outline-primary w-100">Select</button>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Manicure Station #2</h5>
                        <p class="card-text">Comfortable seating with modern equipment.</p>
                        <button class="btn btn-outline-primary w-100">Select</button>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Massage Room</h5>
                        <p class="card-text">A private room with a relaxing atmosphere.</p>
                        <button class="btn btn-outline-primary w-100">Select</button>
                    </div>
                </div>
            </div>

            <!-- Шаг 2: Выбор дня -->
            <div class="col-md-4">
                <h4>Select Date</h4>
                <input type="date" class="form-control mb-4" placeholder="Choose a date">
            </div>

            <!-- Шаг 3: Выбор времени и продолжительности -->
            <div class="col-md-4">
                <h4>Select Time and Duration</h4>
                <label for="startTime" class="form-label">Start Time</label>
                <input type="time" id="startTime" class="form-control mb-4">

                <label for="duration" class="form-label">Duration</label>
                <select id="duration" class="form-select mb-4">
                    <option value="30">30 minutes</option>
                    <option value="60">1 hour</option>
                    <option value="90">1 hour 30 minutes</option>
                    <option value="120">2 hours</option>
                    <option value="150">2 hours 30 minutes</option>
                    <option value="180">3 hours</option>
                </select>

                <button class="btn btn-primary w-100">Confirm Booking</button>
            </div>
        </div>
    </div>
</section>


<!-- Часто задаваемые вопросы -->
<section id="faq" class="bg-light py-5">
    <div class="container">
        <h2 class="text-center mb-5">Frequently Asked Questions</h2>
        <div class="accordion" id="faqAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                        What are the rental terms?
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingTwo">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                        What equipment is included?
                    </button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingThree">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                        How can I book a spot?
                    </button>
                </h2>
                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Контакты и форма обратной связи -->
<section id="contact" class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Contact Us</h2>
        <div class="row">
            <div class="col-md-6">
                <h4>Our Location</h4>
                <p>123 Beauty Street, Cityname, Country</p>
                <p><strong>Phone:</strong> +123 456 7890</p>
                <p><strong>Email:</strong> info@beautycoworking.com</p>
                <div id="map" style="height: 300px; background-color: #e9ecef;">Map Placeholder</div>
            </div>
            <div class="col-md-6">
                <h4>Get in Touch</h4>
                <form>
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" placeholder="Your Name">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" placeholder="Your Email">
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea class="form-control" id="message" rows="4" placeholder="Your Message"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Футер -->
<footer class="bg-dark text-white text-center py-3">
    <div class="container">
        <p>&copy; 2024 Beauty Coworking. All rights reserved.</p>
        <p><a href="#" class="text-white">Privacy Policy</a></p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
