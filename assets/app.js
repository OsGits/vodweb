/* Global UI interactions extracted from inline scripts */
(function(){
  'use strict';
  document.addEventListener('DOMContentLoaded', function(){
    // Horizontal nav scroll with prev/next buttons
    document.querySelectorAll('.nav-scroll').forEach(function (wrap) {
      var track = wrap.querySelector('.nav');
      var prev = wrap.querySelector('.nav-btn.prev');
      var next = wrap.querySelector('.nav-btn.next');
      if (!track || !prev || !next) return;
      function update() {
        var max = track.scrollWidth - track.clientWidth;
        var x = track.scrollLeft;
        prev.disabled = x <= 0;
        next.disabled = x >= (max - 1);
      }
      function step() { return Math.max(120, track.clientWidth * 0.8); }
      update();
      track.addEventListener('scroll', update);
      prev.addEventListener('click', function(){ track.scrollBy({left: -step(), behavior: 'smooth'}); });
      next.addEventListener('click', function(){ track.scrollBy({left: step(), behavior: 'smooth'}); });
      track.addEventListener('keydown', function(e){
        if (e.key === 'ArrowLeft') { track.scrollBy({left: -120, behavior: 'smooth'}); e.preventDefault(); }
        if (e.key === 'ArrowRight') { track.scrollBy({left: 120, behavior: 'smooth'}); e.preventDefault(); }
      });
    });

    // Category modal open/close
    var navToggle = document.querySelector('.nav-toggle');
    var modal = document.getElementById('category-modal');
    var modalClose = modal ? modal.querySelector('.modal-close') : null;
    var firstLink = modal ? modal.querySelector('nav a') : null;
    function openModal() {
      if (!modal) return;
      modal.classList.add('open');
      document.body.classList.add('modal-open');
      if (navToggle) navToggle.setAttribute('aria-expanded', 'true');
      var focusEl = firstLink || modalClose || modal;
      if (focusEl && focusEl.focus) focusEl.focus();
    }
    function closeModal() {
      if (!modal) return;
      modal.classList.remove('open');
      document.body.classList.remove('modal-open');
      if (navToggle) {
        navToggle.setAttribute('aria-expanded', 'false');
        navToggle.focus();
      }
    }
    if (navToggle && modal) {
      navToggle.addEventListener('click', function(){
        if (!modal.classList.contains('open')) openModal(); else closeModal();
      });
    }
    if (modalClose) { modalClose.addEventListener('click', closeModal); }
    if (modal) {
      modal.addEventListener('click', function(e){ if (e.target === modal) closeModal(); });
    }
    document.addEventListener('keydown', function(e){
      if (e.key === 'Escape' && modal && modal.classList.contains('open')) closeModal();
    });

    // Banner slider autoplay
    var slider = document.getElementById('banner-slider');
    if (slider) {
      var slides = slider.querySelectorAll('.banner-slide');
      var count = slides.length;
      if (count > 1) {
        var idx = 0;
        var interval = 3000; // 3秒切换
        setInterval(function(){
          slides[idx].style.opacity = '0';
          slides[idx].style.pointerEvents = 'none';
          idx = (idx + 1) % count;
          slides[idx].style.opacity = '1';
          slides[idx].style.pointerEvents = 'auto';
        }, interval);
      }
    }
  });
})();