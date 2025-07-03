document.addEventListener('DOMContentLoaded', function () {

    // ===================================
    //  HERO GALLERY IMAGE ROTATOR
    // ===================================
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
        'images/img25.jpg', 'images/img26.jpg', 'images/img27.jpg'
    ];
    let heroCurrentlyDisplayed = new Array(heroImageSlots.length).fill(null);

    function getRandomHeroImage(excludeUrls = []) {
        let availableImages = heroImageUrls.filter(url => !excludeUrls.includes(url));
        if (availableImages.length === 0 && heroImageUrls.length > 0) {
            availableImages = heroImageUrls;
        }
        if (availableImages.length === 0) return null;
        const randomIndex = Math.floor(Math.random() * availableImages.length);
        return availableImages[randomIndex];
    }

    function updateHeroImage(slotElement, slotIndex) {
        if (!slotElement || heroImageUrls.length === 0) return;
        const oldImageSrc = heroCurrentlyDisplayed[slotIndex];
        const excludeFromRandom = heroCurrentlyDisplayed.filter((src, i) => i !== slotIndex && src !== null);
        let newImageFullUrl;
        let attempts = 0;
        const maxAttempts = heroImageUrls.length * 2;
        do {
            newImageFullUrl = getRandomHeroImage(excludeFromRandom);
            attempts++;
            if (!newImageFullUrl) break;
        } while (newImageFullUrl === oldImageSrc && attempts < maxAttempts && heroImageUrls.length > heroImageSlots.length);
        if (!newImageFullUrl) {
            newImageFullUrl = heroImageUrls[Math.floor(Math.random() * heroImageUrls.length)];
            if (!newImageFullUrl) return;
        }
        slotElement.style.opacity = '0';
        setTimeout(() => {
            slotElement.src = newImageFullUrl;
            const imageNameOnly = newImageFullUrl.split('/').pop();
            slotElement.alt = `Photography ${imageNameOnly}`;
            slotElement.style.opacity = '1';
            heroCurrentlyDisplayed[slotIndex] = newImageFullUrl;
        }, 500);
    }

    if (heroImageSlots.length > 0 && heroImageUrls.length > 0) {
        heroImageSlots.forEach((slot, index) => {
            if (slot) updateHeroImage(slot, index);
        });
        setInterval(() => {
            if (heroImageSlots.length > 0) {
                const randomSlotIndex = Math.floor(Math.random() * heroImageSlots.length);
                if (heroImageSlots[randomSlotIndex]) updateHeroImage(heroImageSlots[randomSlotIndex], randomSlotIndex);
            }
        }, 5000);
    }

    // ===================================
    //  ABOUT US IMAGE SLIDESHOW
    // ===================================
    const aboutSlideshowImageElement = document.getElementById('about-slideshow-image');
    const aboutImageUrls = [
        'Images/about1.jpg', 'Images/about2.jpg', 'Images/about3.jpg'
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

    // ===================================
    //  PORTFOLIO LIGHTBOX SCRIPT (Corrected and Consolidated)
    // ===================================
    const lightboxElementGlobal = document.getElementById('lightbox'); // Use a unique name
    if (lightboxElementGlobal) {
        const lightboxImgGlobal = lightboxElementGlobal.querySelector('#lightbox-img');
        const lightboxCaptionGlobal = lightboxElementGlobal.querySelector('#lightbox-caption');
        const lightboxCloseBtnGlobal = lightboxElementGlobal.querySelector('.lightbox-close-btn');
        const lightboxPrevBtnGlobal = lightboxElementGlobal.querySelector('.lightbox-prev');
        const lightboxNextBtnGlobal = lightboxElementGlobal.querySelector('.lightbox-next');

        let currentGlobalLightboxImageIndex;
        let allGlobalLightboxTriggerImages = [];

        function updateGlobalLightboxTriggerImages() {
            allGlobalLightboxTriggerImages = Array.from(document.querySelectorAll('img[data-fullsrc]'));
            // console.log("Found lightbox trigger images:", allGlobalLightboxTriggerImages.length); // For debugging
        }

        function openGlobalLightbox(clickedImageElement) {
            // console.log("openGlobalLightbox called with:", clickedImageElement); // For debugging
            if (!clickedImageElement) return;
            const index = allGlobalLightboxTriggerImages.indexOf(clickedImageElement);
            // console.log("Index in trigger images:", index); // For debugging

            if (index >= 0 && index < allGlobalLightboxTriggerImages.length) {
                currentGlobalLightboxImageIndex = index;
                const imgData = allGlobalLightboxTriggerImages[currentGlobalLightboxImageIndex];

                if (imgData && imgData.dataset.fullsrc && lightboxImgGlobal && lightboxCaptionGlobal) {
                    lightboxImgGlobal.src = ""; // Clear previous image
                    lightboxImgGlobal.src = imgData.dataset.fullsrc;
                    lightboxCaptionGlobal.textContent = imgData.dataset.description || imgData.alt || "";
                    lightboxElementGlobal.classList.remove('lightbox-hidden');
                    lightboxElementGlobal.classList.add('lightbox-visible');
                    document.body.style.overflow = 'hidden';
                } else {
                    // console.error("Lightbox image data or elements missing."); // For debugging
                }
            }
        }

        function closeGlobalLightbox() {
            if (lightboxElementGlobal) {
                lightboxElementGlobal.classList.remove('lightbox-visible');
                document.body.style.overflow = 'auto';
                if (lightboxImgGlobal) lightboxImgGlobal.src = "";
            }
        }

        function showNextGlobalLightboxImage() {
            if (allGlobalLightboxTriggerImages.length > 0) {
                const nextIndex = (currentGlobalLightboxImageIndex + 1) % allGlobalLightboxTriggerImages.length;
                openGlobalLightbox(allGlobalLightboxTriggerImages[nextIndex]);
            }
        }

        function showPrevGlobalLightboxImage() {
            if (allGlobalLightboxTriggerImages.length > 0) {
                const prevIndex = (currentGlobalLightboxImageIndex - 1 + allGlobalLightboxTriggerImages.length) % allGlobalLightboxTriggerImages.length;
                openGlobalLightbox(allGlobalLightboxTriggerImages[prevIndex]);
            }
        }

        function handleGlobalImageClickForLightbox(e) {
            updateGlobalLightboxTriggerImages();
            openGlobalLightbox(e.currentTarget);
        }

        function initializeGlobalLightboxTriggers() {
            updateGlobalLightboxTriggerImages();
            allGlobalLightboxTriggerImages.forEach((imgElement) => {
                imgElement.removeEventListener('click', handleGlobalImageClickForLightbox); // Prevent multiple listeners
                imgElement.addEventListener('click', handleGlobalImageClickForLightbox);
            });
        }

        if (lightboxImgGlobal && lightboxCaptionGlobal && lightboxCloseBtnGlobal && lightboxPrevBtnGlobal && lightboxNextBtnGlobal) {
            initializeGlobalLightboxTriggers();
            lightboxCloseBtnGlobal.addEventListener('click', closeGlobalLightbox);
            lightboxPrevBtnGlobal.addEventListener('click', showPrevGlobalLightboxImage);
            lightboxNextBtnGlobal.addEventListener('click', showNextGlobalLightboxImage);
            lightboxElementGlobal.addEventListener('click', (e) => {
                if (e.target === lightboxElementGlobal) closeGlobalLightbox();
            });
            document.addEventListener('keydown', (e) => {
                if (lightboxElementGlobal.classList.contains('lightbox-visible')) {
                    if (e.key === 'Escape') closeGlobalLightbox();
                    if (e.key === 'ArrowRight') showNextGlobalLightboxImage();
                    if (e.key === 'ArrowLeft') showPrevGlobalLightboxImage();
                }
            });
        } else {

        }
    }
    //  TESTIMONIAL SLIDER SCRIPT
    const testimonialSliderWrapperGlobal = document.querySelector('.testimonial-slider-wrapper');

    if (testimonialSliderWrapperGlobal) {
        const sliderContainerGlobal = testimonialSliderWrapperGlobal.querySelector('.testimonial-slider-container');
        const sliderTrackGlobal = sliderContainerGlobal ? sliderContainerGlobal.querySelector('.testimonial-slider') : null;
        const slidesGlobal = sliderTrackGlobal ? Array.from(sliderTrackGlobal.querySelectorAll('.testimonial-slide')) : [];

        const navControlsGlobal = testimonialSliderWrapperGlobal.querySelector('.slider-nav-controls');
        const prevButtonGlobal = navControlsGlobal ? navControlsGlobal.querySelector('.prev-arrow') : null;
        const nextButtonGlobal = navControlsGlobal ? navControlsGlobal.querySelector('.next-arrow') : null;
        const dotsContainerGlobal = navControlsGlobal ? navControlsGlobal.querySelector('.slider-nav-dots') : null;

        let currentTestimonialIndex = 0;
        const totalTestimonialSlides = slidesGlobal.length;

        function updateTestimonialSliderPosition() {
            if (sliderTrackGlobal) {
                sliderTrackGlobal.style.transform = `translateX(-${currentTestimonialIndex * 100}%)`;
            }
            updateTestimonialDots();
            updateArrowStates();
        }

        function updateTestimonialDots() {
            if (!dotsContainerGlobal || totalTestimonialSlides <= 1) return;
            const dots = dotsContainerGlobal.querySelectorAll('.dot');
            dots.forEach((dot, index) => {
                dot.classList.toggle('active-dot', index === currentTestimonialIndex);
            });
        }

        function createTestimonialDots() {
            if (!dotsContainerGlobal || totalTestimonialSlides <= 1) {
                if (navControlsGlobal) navControlsGlobal.style.display = 'none';
                return;
            }
            if (navControlsGlobal) navControlsGlobal.style.display = 'flex'; // Or 'block'

            dotsContainerGlobal.innerHTML = '';
            for (let i = 0; i < totalTestimonialSlides; i++) {
                const dotButton = document.createElement('button'); // Use button for accessibility
                dotButton.classList.add('dot');
                dotButton.setAttribute('type', 'button');
                dotButton.setAttribute('aria-label', `Go to testimonial ${i + 1}`);
                dotButton.addEventListener('click', () => {
                    currentTestimonialIndex = i;
                    updateTestimonialSliderPosition();
                });
                dotsContainerGlobal.appendChild(dotButton);
            }
        }

        function updateArrowStates() { // Optional: Disable arrows at ends if not looping
            if (!prevButtonGlobal || !nextButtonGlobal || totalTestimonialSlides <= 1) return;
            // If you don't want looping, disable buttons:
            // prevButtonGlobal.disabled = currentTestimonialIndex === 0;
            // nextButtonGlobal.disabled = currentTestimonialIndex === totalTestimonialSlides - 1;
        }

        function showNextTestimonialSlide() {
            currentTestimonialIndex = (currentTestimonialIndex + 1) % totalTestimonialSlides; // Loops
            updateTestimonialSliderPosition();
        }

        function showPrevTestimonialSlide() {
            currentTestimonialIndex = (currentTestimonialIndex - 1 + totalTestimonialSlides) % totalTestimonialSlides; // Loops
            updateTestimonialSliderPosition();
        }

        // Initialization
        if (sliderTrackGlobal && totalTestimonialSlides > 0) {
            createTestimonialDots();
            updateTestimonialSliderPosition(); // Set initial position and dots

            if (nextButtonGlobal) {
                nextButtonGlobal.addEventListener('click', showNextTestimonialSlide);
            }
            if (prevButtonGlobal) {
                prevButtonGlobal.addEventListener('click', showPrevTestimonialSlide);
            }

            // Optional: Auto-slide
            // let autoSlideInterval = setInterval(showNextTestimonialSlide, 7000); // Change every 7 seconds
            // testimonialSliderWrapperGlobal.addEventListener('mouseenter', () => clearInterval(autoSlideInterval));
            // testimonialSliderWrapperGlobal.addEventListener('mouseleave', () => autoSlideInterval = setInterval(showNextTestimonialSlide, 7000));

        } else if (testimonialSliderWrapperGlobal) {
            if (navControlsGlobal) navControlsGlobal.style.display = 'none';
        }
    } else {
        // console.log("Testimonial slider wrapper not found.");
    }

    //  SMOOTH SCROLL FOR NAV LINKS
    document.querySelectorAll('header nav ul li a[href^="#"], a.cta-button[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId && targetId.startsWith('#') && targetId.length > 1) {
                e.preventDefault();
                try {
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        targetElement.scrollIntoView({ behavior: 'smooth' });
                    }
                } catch (error) { /* console.warn("Smooth scroll error:", error); */ }
            }
        });
    });

    // whatsapp contact from script
    const sendWhatsappBtn = document.getElementById('send-whatsapp-btn');
    if (sendWhatsappBtn) {
        const inputName = document.getElementById('whatsapp_name');
        const inputSubject = document.getElementById('whatsapp_subject');
        const inputMessage = document.getElementById('whatsapp_message');
        const formStatusMsg = document.getElementById('form-status-message');

        sendWhatsappBtn.addEventListener('click', function (event) {
            event.preventDefault();
            const yourWhatsAppNumber = '94724178024';
            const name = inputName ? inputName.value.trim() : '';
            const subject = inputSubject ? inputSubject.value.trim() : '';
            const message = inputMessage ? inputMessage.value.trim() : '';

            // --- Validation ---
            if (name === '' || subject === '' || message === '') {
                if (formStatusMsg) {
                    formStatusMsg.className = 'error';
                    formStatusMsg.innerHTML = 'Please fill out all fields before sending.';
                }
                return;
            } else {
                if (formStatusMsg) {
                    formStatusMsg.innerHTML = '';
                    formStatusMsg.className = '';
                }
            }

            let preFilledMessage =
                `Hello Elegant Image,\n\n` +
                `*Name:* ${name}\n` +
                `*Subject:* ${subject}\n\n` +
                `*Message:*\n${message}\n\n` +
                `---\nSent from your website contact form.`;

            let encodedMessage = encodeURIComponent(preFilledMessage);

            const whatsappUrl = `https://wa.me/${yourWhatsAppNumber}?text=${encodedMessage}`;

            window.open(whatsappUrl, '_blank');

            if (formStatusMsg) {
                formStatusMsg.className = 'success';
                formStatusMsg.innerHTML = 'WhatsApp is opening... Please send your message there.';
            }
        });
    }
});