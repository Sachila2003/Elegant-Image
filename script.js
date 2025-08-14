document.addEventListener('DOMContentLoaded', function () {

    // ================================================================
    //              1. HERO GALLERY IMAGE ROTATOR
    // ================================================================
    const heroImageSlots = [
        document.querySelector('#slot1 img'),
        document.querySelector('#slot2 img'),
        document.querySelector('#slot3 img'),
        document.querySelector('#slot4 img')
    ].filter(slot => slot !== null);

    const heroImageUrls = [
        'images/img01.jpg', 'images/img02.jpg', 'images/img03.jpg', 'images/img04.jpg',
        'images/img05.jpg', 'images/img06.jpg', 'images/img07.jpg', 'images/img08.jpg',
        'images/img09.jpg', 'images/img10.jpg', 'images/img11.jpg', 'images/img12.jpg',
        'images/img13.jpg', 'images/img14.jpg', 'images/img15.jpg', 'images/img16.jpg',
        'images/img17.jpg', 'images/img18.jpg', 'images/img19.jpg', 'images/img20.jpg',
        'images/img21.jpg', 'images/img22.jpg', 'images/img23.jpg', 'images/img24.jpg',
        'images/img25.jpg', 'images/img26.jpg', 'images/img27.jpg', 'images/img28.jpg',
        'images/img29.jpg', 'images/img31.jpg', 'images/img32.jpg',
        'images/img33.jpg', 'images/img34.jpg', 'images/img35.jpg', 'images/img36.jpg',
        'images/img37.jpg', 'images/img38.jpg', 'images/img39.jpg', 'images/img40.jpg',
        'images/img41.jpg', 'images/img42.jpg', 'images/img43.jpg', 'images/img44.jpg',
        'images/img45.jpg', 'images/img46.jpg', 'images/img47.jpg'
    ];
    let heroCurrentlyDisplayed = new Array(heroImageSlots.length).fill(null);

    function getRandomHeroImage(excludeUrls = []) {
        let availableImages = heroImageUrls.filter(url => !excludeUrls.includes(url));
        if (availableImages.length === 0) availableImages = heroImageUrls;
        if (availableImages.length === 0) return null;
        return availableImages[Math.floor(Math.random() * availableImages.length)];
    }

    function updateHeroImage(slotElement, slotIndex) {
        if (!slotElement || heroImageUrls.length === 0) return;
        const newImageUrl = getRandomHeroImage(heroCurrentlyDisplayed);
        if (!newImageUrl) return;

        slotElement.style.opacity = '0';
        setTimeout(() => {
            slotElement.src = newImageUrl;
            slotElement.alt = `Photography by Elegant Image`;
            slotElement.style.opacity = '1';
            heroCurrentlyDisplayed[slotIndex] = newImageUrl;
        }, 500);
    }

    if (heroImageSlots.length > 0 && heroImageUrls.length > 0) {
        heroImageSlots.forEach((slot, index) => updateHeroImage(slot, index));
        setInterval(() => {
            const randomSlotIndex = Math.floor(Math.random() * heroImageSlots.length);
            updateHeroImage(heroImageSlots[randomSlotIndex], randomSlotIndex);
        }, 5000);
    }

    // ================================================================
    //              2. ABOUT US IMAGE SLIDESHOW
    // ================================================================
    const aboutSlideshowImageElement = document.getElementById('about-slideshow-image');
    const aboutImageUrls = [
        'Images/about.jpg'
    ];
    const validAboutImageUrls = aboutImageUrls.filter(src => src && src.trim() !== "");

    if (aboutSlideshowImageElement && validAboutImageUrls.length > 0) {
        let currentAboutImageIndex = 0;
        aboutSlideshowImageElement.src = validAboutImageUrls[currentAboutImageIndex];
        aboutSlideshowImageElement.alt = `About Us Image ${currentAboutImageIndex + 1}`;
        aboutSlideshowImageElement.style.opacity = '1';
        if (validAboutImageUrls.length > 1) {
            function changeAboutImage() {
                currentAboutImageIndex = (currentAboutImageIndex + 1) % validAboutImageUrls.length;
                aboutSlideshowImageElement.style.opacity = '0';
                setTimeout(() => {
                    aboutSlideshowImageElement.src = validAboutImageUrls[currentAboutImageIndex];
                    aboutSlideshowImageElement.alt = `About Us Image ${currentAboutImageIndex + 1}`;
                    aboutSlideshowImageElement.onload = () => {
                        aboutSlideshowImageElement.style.opacity = '1';
                        aboutSlideshowImageElement.onload = null;
                    };
                    setTimeout(() => { if (aboutSlideshowImageElement.style.opacity !== '1') aboutSlideshowImageElement.style.opacity = '1'; }, 50);
                }, 700);
            }
            setInterval(changeAboutImage, 4000);
        }
    }

    // ================================================================
    //              3. THEME TOGGLE LOGIC
    // ================================================================
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        const themeIcon = document.getElementById('themeIcon');
        const html = document.documentElement;
        
        const savedTheme = localStorage.getItem('theme') || 
            (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        
        html.setAttribute('data-bs-theme', savedTheme);
        updateThemeIcon(savedTheme);
        
        themeToggle.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            html.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });
        
        function updateThemeIcon(theme) {
            if (themeIcon) {
                if (theme === 'light') {
                    themeIcon.classList.remove('fa-sun');
                    themeIcon.classList.add('fa-moon');
                } else {
                    themeIcon.classList.remove('fa-moon');
                    themeIcon.classList.add('fa-sun');    
                }
            }
        }
    }

    // ================================================================
    //              4. MOBILE NAVIGATION (HAMBURGER MENU)
    // ================================================================
    const navMenu = document.getElementById('nav-menu');
    const navToggle = document.getElementById('nav-toggle');
    const navClose = document.getElementById('nav-close');
    const navLinks = document.querySelectorAll('.nav-link');

    if (navToggle && navMenu) {
        navToggle.addEventListener('click', () => {
            navMenu.classList.add('show-menu');
        });
    }

    if (navClose && navMenu) {
        navClose.addEventListener('click', () => {
            navMenu.classList.remove('show-menu');
        });
    }

    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (navMenu && navMenu.classList.contains('show-menu')) {
                navMenu.classList.remove('show-menu');
            }
        });
    });

    // ================================================================
    //              5. TESTIMONIAL SLIDER
    // ================================================================
    const sliderWrapper = document.querySelector('.testimonial-slider-wrapper');
    if (sliderWrapper) {
        const sliderTrack = sliderWrapper.querySelector('.testimonial-slider');
        const slides = sliderTrack ? Array.from(sliderTrack.querySelectorAll('.testimonial-slide')) : [];
        const nextBtn = sliderWrapper.querySelector('.next-arrow');
        const prevBtn = sliderWrapper.querySelector('.prev-arrow');
        const dotsContainer = sliderWrapper.querySelector('.slider-nav-dots');
        let currentIndex = 0;

        if (slides.length > 1) {
            dotsContainer.innerHTML = '';
            slides.forEach((_, i) => {
                const dot = document.createElement('button');
                dot.classList.add('dot');
                dot.setAttribute('aria-label', `Go to slide ${i + 1}`);
                dot.addEventListener('click', () => {
                    currentIndex = i;
                    updateSlider();
                });
                dotsContainer.appendChild(dot);
            });
            const dots = dotsContainer.querySelectorAll('.dot');

            function updateSlider() {
                sliderTrack.style.transform = `translateX(-${currentIndex * 100}%)`;
                dots.forEach(dot => dot.classList.remove('active-dot'));
                dots[currentIndex].classList.add('active-dot');
            }

            nextBtn.addEventListener('click', () => {
                currentIndex = (currentIndex + 1) % slides.length;
                updateSlider();
            });

            prevBtn.addEventListener('click', () => {
                currentIndex = (currentIndex - 1 + slides.length) % slides.length;
                updateSlider();
            });

            updateSlider();
        }
    }

    // ================================================================
    //              6. WHATSAPP CONTACT FORM
    // ================================================================
    const sendWhatsappBtn = document.getElementById('send-whatsapp-btn');
    if (sendWhatsappBtn) {
        sendWhatsappBtn.addEventListener('click', function (event) {
            event.preventDefault();
            const yourWhatsAppNumber = '94742715484';
            const name = document.getElementById('whatsapp_name').value.trim();
            const subject = document.getElementById('whatsapp_subject').value.trim();
            const message = document.getElementById('whatsapp_message').value.trim();
            const formStatusMsg = document.getElementById('form-status-message');

            if (name === '' || subject === '' || message === '') {
                if (formStatusMsg) {
                    formStatusMsg.textContent = 'Please fill out all fields before sending.';
                    formStatusMsg.style.color = 'red';
                }
                return;
            }

            let preFilledMessage = `Hello Elegant Image,\n\n*Name:* ${name}\n*Subject:* ${subject}\n\n*Message:*\n${message}`;
            const whatsappUrl = `https://wa.me/${yourWhatsAppNumber}?text=${encodeURIComponent(preFilledMessage)}`;
            window.open(whatsappUrl, '_blank');
        });
    }

    // ================================================================
    //              7. COPYRIGHT YEAR
    // ================================================================
    const copyrightYear = document.getElementById('copyright-year');
    if(copyrightYear) {
        copyrightYear.textContent = new Date().getFullYear();
    }

});