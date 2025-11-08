<?php
/**
 * Banner组件 - 动态加载版本
 * 通过API接口获取横幅数据，支持轮播功能
 */
?>
<div class="banner-top">
  <div id="banner-slider" class="banner-slider">
    <!-- API动态加载横幅内容 -->
    <div class="banner-loading">加载中...</div>
  </div>
  <script type="text/javascript">
    // 动态加载横幅数据
    document.addEventListener('DOMContentLoaded', function() {
      const slider = document.getElementById('banner-slider');
      const loading = slider.querySelector('.banner-loading');
      
      fetch('/127.1/api/banners.php')
        .then(response => {
          if (!response.ok) {
            throw new Error('API请求失败');
          }
          return response.json();
        })
        .then(data => {
          // 移除加载提示
          if (loading) loading.remove();
          
          // 检查数据是否有效
          if (!data.code || data.code !== 200 || !data.data || !Array.isArray(data.data)) {
            throw new Error('数据格式错误');
          }
          
          const banners = data.data;
          
          // 如果没有横幅数据，使用默认横幅
          if (banners.length === 0) {
            const defaultBanner = document.createElement('div');
            defaultBanner.className = 'banner-slide';
            defaultBanner.className = 'banner-slide active';
            defaultBanner.innerHTML = `
              <a href="/127.1/index.php">
                <img class="banner-img" src="/127.1/assets/placeholder.svg" alt="默认横幅" />
              </a>
            `;
            slider.appendChild(defaultBanner);
            return;
          }
          
          // 渲染横幅数据
          banners.forEach((banner, index) => {
            const slide = document.createElement('div');
            slide.className = 'banner-slide';
            slide.className = `banner-slide ${banner.active ? 'active' : ''}`;
             slide.innerHTML = `
               <a href="${banner.link || '/127.1/index.php'}">
                 <img class="banner-img" src="${banner.image}" alt="${banner.title || '横幅'}" />
               </a>
             `;
            slider.appendChild(slide);
          });
          
          // 如果有多个横幅，启用轮播效果
          if (banners.length > 1) {
            let currentIndex = 0;
            const slides = slider.querySelectorAll('.banner-slide');
            const interval = 3000; // 3秒切换
            
            setInterval(() => {
              // 移除当前横幅的active类
              slides[currentIndex].classList.remove('active');
              
              // 计算下一个横幅索引
              currentIndex = (currentIndex + 1) % slides.length;
              
              // 为下一个横幅添加active类
              slides[currentIndex].classList.add('active');
            }, interval);
          }
        })
        .catch(error => {
          console.error('横幅加载失败:', error);
          // 移除加载提示，显示错误信息
          if (loading) {
            loading.textContent = '加载失败';
          }
        });
    });
  </script>
</div>
