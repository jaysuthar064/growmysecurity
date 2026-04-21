/**
 * Case Studies Interactivity - Intelligence Hub
 * Handles scroll progress, sticky navigation, and reveal effects.
 */
document.addEventListener('DOMContentLoaded', function() {
    const progressBar = document.querySelector('.gms-cs-scroll-progress-bar');
    const sections = document.querySelectorAll('.gms-cs-report-section');

    // 1. Scroll Progress Logic
    window.addEventListener('scroll', () => {
        const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
        const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const scrolled = (winScroll / height) * 100;
        
        if (progressBar) {
            progressBar.style.width = scrolled + "%";
        }
    });

    // 2. Reveal Animations (Intersection Observer)
    const revealOptions = {
        threshold: 0.1,
        rootMargin: "0px 0px -100px 0px"
    };

    const revealObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('gms-revealed');
                observer.unobserve(entry.target);
            }
        });
    }, revealOptions);

    sections.forEach(section => {
        section.classList.add('gms-reveal-hidden');
        revealObserver.observe(section);
    });

    // 3. 3D Tilt Effect on Visuals
    const visuals = document.querySelectorAll('.gms-cs-report-visual img');
    visuals.forEach(visual => {
        visual.addEventListener('mousemove', (e) => {
            const { left, top, width, height } = visual.getBoundingClientRect();
            const x = (e.clientX - left) / width;
            const y = (e.clientY - top) / height;
            
            const moveX = (x - 0.5) * 10;
            const moveY = (y - 0.5) * 10;
            
            visual.style.transform = `perspective(1000px) rotateY(${moveX}deg) rotateX(${-moveY}deg) scale3d(1.02, 1.02, 1.02)`;
        });
        
        visual.addEventListener('mouseleave', () => {
            visual.style.transform = `perspective(1000px) rotateY(0deg) rotateX(0deg) scale3d(1, 1, 1)`;
        });
    });
});
