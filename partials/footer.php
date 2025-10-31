</main>
<footer class="site-footer">
  <div class="container">
    <p>数据来源于接口，本站不存储任何视频资源。</p>
  </div>
</footer>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.nav-scroll').forEach(function (wrap) {
      var track = wrap.querySelector('.nav');
      var prev = wrap.querySelector('.nav-btn.prev');
      var next = wrap.querySelector('.nav-btn.next');
      function update() {
        var max = track.scrollWidth - track.clientWidth;
        var x = track.scrollLeft;
        prev.disabled = x <= 0;
        next.disabled = x >= (max - 1);
      }
      function step() {
        return Math.max(120, track.clientWidth * 0.8);
      }
      update();
      track.addEventListener('scroll', update);
      prev.addEventListener('click', function(){ track.scrollBy({left: -step(), behavior: 'smooth'}); });
      next.addEventListener('click', function(){ track.scrollBy({left: step(), behavior: 'smooth'}); });
      track.addEventListener('keydown', function(e){
        if (e.key === 'ArrowLeft') { track.scrollBy({left: -120, behavior: 'smooth'}); e.preventDefault(); }
        if (e.key === 'ArrowRight') { track.scrollBy({left: 120, behavior: 'smooth'}); e.preventDefault(); }
      });
    });
  });
</body>
</html>