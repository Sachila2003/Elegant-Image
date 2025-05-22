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
            if(slot) updateHeroImage(slot, index);
        });
        setInterval(() => {
            if (heroImageSlots.length > 0) {
                const randomSlotIndex = Math.floor(Math.random() * heroImageSlots.length);
                if(heroImageSlots[randomSlotIndex]) updateHeroImage(heroImageSlots[randomSlotIndex], randomSlotIndex);
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

// ===================================
//  TESTIMONIAL SLIDER (REFINED)
// ===================================
const testimonialSliderContainer = document.querySelector('.testimonial-slider-container');

if (testimonialSliderContainer) {
    const slider = testimonialSliderContainer.querySelector('.testimonial-slider');
    const slides = slider ? Array.from(slider.querySelectorAll('.testimonial-slide')) : [];
    const prevButton = testimonialSliderContainer.querySelector('.slider-nav-arrows .prev-arrow');
    const nextButton = testimonialSliderContainer.querySelector('.slider-nav-arrows .next-arrow');
    const dotsContainer = testimonialSliderContainer.querySelector('.slider-nav-dots');

    let currentIndex = 0;
    const totalSlides = slides.length;

    function updateSliderPosition() {
        if (slider) {
            slider.style.transform = `translateX(-${currentIndex * 100}%)`;
        }
        updateDots();
    }

    function updateDots() {
        if (!dotsContainer || totalSlides <= 1) return;
        const dots = dotsContainer.querySelectorAll('.dot');
        dots.forEach((dot, index) => {
            dot.classList.toggle('active-dot', index === currentIndex);
        });
    }

    function createDots() {
        if (!dotsContainer || totalSlides <= 1) {
            if (dotsContainer) dotsContainer.style.display = 'none';
            if (prevButton && nextButton && totalSlides <= 1) {
                if(prevButton) prevButton.style.display = 'none';
                if(nextButton) nextButton.style.display = 'none';
            }
            return;
        }

        dotsContainer.innerHTML = ''; // Clear existing dots
        for (let i = 0; i < totalSlides; i++) {
            const dot = document.createElement('button');
            dot.classList.add('dot');
            dot.setAttribute('aria-label', `Go to testimonial ${i + 1}`);
            dot.addEventListener('click', () => {
                currentIndex = i;
                updateSliderPosition();
            });
            dotsContainer.appendChild(dot);
        }
    }

    function showNextSlide() {
        currentIndex = (currentIndex + 1) % totalSlides;
        updateSliderPosition();
    }

    function showPrevSlide() {
        currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
        updateSliderPosition();
    }

    // Initialization
    if (slider && totalSlides > 0) {
        createDots();
        updateSliderPosition(); // Show the first slide

        if (nextButton) {
            nextButton.addEventListener('click', showNextSlide);
        } else {
            console.warn("Testimonial slider 'next' button not found.");
        }

        if (prevButton) {
            prevButton.addEventListener('click', showPrevSlide);
        } else {
            console.warn("Testimonial slider 'previous' button not found.");
        }

    } else if (testimonialSliderContainer) { // If container exists but no slides or slider element
        const navArrows = testimonialSliderContainer.querySelector('.slider-nav-arrows');
        if (navArrows) navArrows.style.display = 'none';
        if (dotsContainer) dotsContainer.style.display = 'none';
        if (slider && totalSlides === 0) {
            // console.log("Testimonial slider found, but no slides to display.");
        } else if (!slider) {
            // console.error("Element with class '.testimonial-slider' not found inside '.testimonial-slider-container'.");
        }
    }
} else {
    // console.log("Element with class '.testimonial-slider-container' not found.");
}
// ===================================
//  END TESTIMONIAL SLIDER
// ===================================

// ... (Your other JS: Hero, About, Portfolio Lightbox, Smooth Scroll should be here,
//      also inside the main DOMContentLoaded listener, but separate from this slider logic) ...
    // ===================================
    //  SMOOTH SCROLL FOR NAV LINKS
    // ===================================
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

}); // End DOMContentLoaded