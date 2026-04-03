<?php
$itemId = null;

if (isset($_GET['id'])) {
    $itemId = $_GET['id'];
} else {
    // URL-dən "438" kimi ID-ni tutmaq üçün (məsələn: item.php?438)
    $queryString = $_SERVER['QUERY_STRING'] ?? '';
    $parts = explode('&', $queryString);
    if (!empty($parts[0]) && is_numeric($parts[0])) {
        $itemId = $parts[0];
    }
}

// Meta Teqlər üçün Default Dəyərlər
$ogTitle = "TapAsan - Al və Sat";
$ogDesc = "Tətbiqdə daha rahat al və sat. TapAsan mobil tətbiqini yüklə.";
$ogImage = "https://lh3.googleusercontent.com/a/ACg8ocKX5FmP0tiurAVrFdymeYueA5SRXU6BoS-Gl0Yo87khdX5RsmI=s360-c-no";
$ogUrl = "https://ilafar.github.io/TapasanItemHostTest/index.php" . ($itemId ? "?{$itemId}" : "");

if ($itemId) {
    $apiUrl = "https://tapasan.shop/api/Listing/" . $itemId;
    
    // API-dən məlumatı cURL ilə çəkirik
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3); // Maksimum 3 saniyə gözləsin
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        if (isset($data['success']) && $data['success'] === true && isset($data['data'])) {
            $item = $data['data'];
            
            if (!empty($item['title'])) {
                $ogTitle = htmlspecialchars($item['title']) . " - TapAsan";
            }
            if (!empty($item['description'])) {
                // Təsviri qısaldaq ki whatsapp-da səliqəli görünsün
                $ogDesc = htmlspecialchars(mb_substr($item['description'], 0, 160)) . "...";
            }
            if (!empty($item['images']) && count($item['images']) > 0) {
                $img = $item['images'][0];
                $src = $img['originalUrl'] ?? $img['mediumUrl'] ?? $img['thumbnailUrl'] ?? null;
                if ($src) {
                    if (strpos($src, 'http') !== 0) {
                        $src = 'https://tapasan.shop/' . ltrim($src, '/');
                    }
                    $ogImage = $src;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $ogTitle ?></title>
    
    <!-- Open Graph (WhatsApp, Facebook, Telegram və s. üçün) -->
    <meta property="og:title" content="<?= $ogTitle ?>" />
    <meta property="og:description" content="<?= $ogDesc ?>" />
    <meta property="og:image" content="<?= $ogImage ?>" />
    <meta property="og:url" content="<?= $ogUrl ?>" />
    <meta property="og:type" content="website" />
    <meta name="twitter:card" content="summary_large_image" />
    
    <link rel="icon" href="https://lh3.googleusercontent.com/a/ACg8ocKX5FmP0tiurAVrFdymeYueA5SRXU6BoS-Gl0Yo87khdX5RsmI=s360-c-no">
    <!-- Caching əngəlləmək üçün (Bəzən botlar keşlənmiş şəkli saxlayır) -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />

    <style>
        :root {
            --primary-color: #5555ff;
            --bg-color: #f8f9fa;
            --text-color: #333;
            --text-secondary: #666;
            --white: #ffffff;
            --radius-md: 12px;
            --radius-sm: 8px;
            --font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--bg-color);
            color: var(--text-color);
            padding-bottom: 80px;
        }

        .app-banner {
            background: var(--white);
            padding: 10px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #eee;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .app-banner-content { display: flex; align-items: center; gap: 12px; }
        .app-icon {
            width: 40px; height: 40px; background: #eee; border-radius: 8px;
            background-image: url('https://lh3.googleusercontent.com/a/ACg8ocKX5FmP0tiurAVrFdymeYueA5SRXU6BoS-Gl0Yo87khdX5RsmI=s360-c-no');
            background-size: cover;
        }
        .app-icon:empty { background: linear-gradient(135deg, #6dd5ed, #2193b0); }
        .app-info h3 { font-size: 14px; font-weight: 600; margin-bottom: 2px; }
        .app-info p { font-size: 12px; color: var(--text-secondary); }
        .btn-open {
            background: var(--primary-color); color: white; border: none;
            padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 500;
            text-decoration: none;
        }

        .container { max-width: 600px; margin: 0 auto; background: var(--bg-color); }
        .slider-container {
            position: relative; width: 100%; height: 300px; background: #e0e0e0;
            overflow: hidden; border-radius: 16px; margin-top: 16px;
        }
        .slider-wrapper {
            display: flex; height: 100%; overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none;
        }
        .slider-wrapper::-webkit-scrollbar { display: none; }
        .slide { flex: 0 0 100%; height: 100%; scroll-snap-align: center; display: flex; align-items: center; justify-content: center; }
        .slide img { width: 100%; height: 100%; object-fit: cover; }
        .slide-counter {
            position: absolute; bottom: 16px; right: 16px; background: rgba(255, 255, 255, 0.9);
            padding: 4px 12px; border-radius: 16px; font-size: 12px; font-weight: 600; color: #333;
        }

        .product-info { padding: 16px; background: var(--bg-color); }
        .price { font-size: 24px; font-weight: 700; color: #000; margin-bottom: 8px; }
        .title { font-size: 18px; font-weight: 500; color: #333; margin-bottom: 8px; line-height: 1.4; }
        .meta { font-size: 14px; color: var(--text-secondary); margin-bottom: 24px; }
        .section { margin-bottom: 24px; }
        .section-label { font-size: 14px; color: var(--text-secondary); margin-bottom: 8px; font-weight: 500; }
        .category-chip { display: inline-flex; align-items: center; background: var(--white); padding: 10px 16px; border-radius: var(--radius-sm); font-size: 14px; font-weight: 500; color: #333; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05); gap: 8px; }
        .description-box { background: var(--white); padding: 16px; border-radius: var(--radius-md); font-size: 14px; line-height: 1.6; color: #444; white-space: pre-wrap; }
        .location-box { background: var(--white); padding: 12px 16px; border-radius: var(--radius-md); font-size: 14px; margin-bottom: 8px; display: flex; align-items: center; }

        .bottom-bar {
            position: fixed; bottom: 0; left: 0; right: 0; margin: 0 auto; width: 100%; max-width: 600px;
            background: var(--white); padding: 12px 16px; border-top: 1px solid #eee;
            padding-bottom: calc(12px + env(safe-area-inset-bottom)); display: flex; justify-content: center; z-index: 1000;
        }
        .contact-btn {
            width: 100%; background: linear-gradient(90deg, #6c8cff, #8a5eff); color: white; border: none;
            padding: 14px; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer;
            text-align: center; box-sizing: border-box; margin-top: 0;
        }
    </style>
</head>
<body>
    <div class="app-banner">
        <div class="app-banner-content">
            <div class="app-icon">
                <img src="https://lh3.googleusercontent.com/a/ACg8ocKX5FmP0tiurAVrFdymeYueA5SRXU6BoS-Gl0Yo87khdX5RsmI=s360-c-no" style="width:100%; height:100%; border-radius:8px; display:none;" id="appIconImg">
            </div>
            <div class="app-info">
                <h3>TapAsan - Al və Sat</h3>
                <p>Tətbiqdə daha rahat</p>
            </div>
        </div>
        <a href="#" class="btn-open">Yüklə</a>
    </div>

    <div class="container" id="mainContainer" style="display:none;">
        <div class="slider-container">
            <div class="slider-wrapper" id="imageSlider"></div>
            <div class="slide-counter" id="slideCounter">1/1</div>
        </div>
        <div class="product-info">
            <div class="price" id="itemPrice">-- AZN</div>
            <div class="title" id="itemTitle">Yüklənir...</div>
            <div class="meta" id="itemMeta"></div>
            <div class="section">
                <div class="section-label">Kateqoriya</div>
                <div class="category-chip"><span id="itemCategory">...</span></div>
            </div>
            <div class="section">
                <div class="section-label">Elan təsviri</div>
                <div class="description-box" id="itemDescription">Yüklənir...</div>
            </div>
            <div class="section">
                <div class="section-label">Şəhər</div>
                <div class="location-box" id="itemCity"></div>
            </div>
        </div>
        <div class="bottom-bar">
            <div class="contact-btn" id="contactBtn">Yüklə</div>
        </div>
    </div>

    <div id="loading" style="text-align:center; padding: 50px;">Yüklənir...</div>
    <div id="error" style="text-align:center; padding: 50px; color: red; display:none;"></div>

    <script>
        const BASE_URL = 'https://tapasan.shop/api/';

        function formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            const now = new Date();
            const diffMs = now - date;
            const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
            if (diffDays === 0) return `Bu gün, ${date.getHours()}:${String(date.getMinutes()).padStart(2, '0')}`;
            if (diffDays === 1) return `Dünən, ${date.getHours()}:${String(date.getMinutes()).padStart(2, '0')}`;
            return `${date.getDate()}.${date.getMonth() + 1}.${date.getFullYear()}`;
        }

        const urlParams = new URLSearchParams(window.location.search);
        let itemId = urlParams.get('id');
        if (!itemId) {
            const keys = Array.from(urlParams.keys());
            if (keys.length > 0 && !isNaN(keys[0])) {
                itemId = keys[0];
            }
        }

        function redirectToStore() {
            const userAgent = navigator.userAgent || navigator.vendor || window.opera;
            const androidUrl = "https://play.google.com/store/apps/details?id=com.ecommerce.tapasan";
            const iosUrl = "https://apps.apple.com/app/id6756875280";
            if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {
                window.location.href = iosUrl;
            } else {
                window.location.href = androidUrl;
            }
        }

        const bannerBtn = document.querySelector('.btn-open');
        if (bannerBtn) {
            bannerBtn.onclick = (e) => {
                e.preventDefault();
                redirectToStore();
            };
        }

        async function fetchItemDetails() {
            if (!itemId) {
                document.getElementById('loading').style.display = 'none';
                document.getElementById('error').textContent = "Məhsul ID tapılmadı.";
                document.getElementById('error').style.display = 'block';
                return;
            }

            try {
                const response = await fetch(`${BASE_URL}Listing/${itemId}`);
                const data = await response.json();

                const contactBtn = document.getElementById('contactBtn');
                if (contactBtn) {
                    contactBtn.innerText = "Əlaqə saxla";
                    contactBtn.onclick = () => redirectToStore();
                }

                if (data.success && data.data) {
                    renderItem(data.data);
                } else {
                    throw new Error('Data not successful');
                }
            } catch (err) {
                console.error(err);
                document.getElementById('loading').style.display = 'none';
                document.getElementById('error').style.display = 'block';
            }
        }

        function renderItem(item) {
            document.getElementById('loading').style.display = 'none';
            document.getElementById('mainContainer').style.display = 'block';

            document.title = `${item.title} | TapAsan`;

            const slider = document.getElementById('imageSlider');
            const counter = document.getElementById('slideCounter');
            slider.innerHTML = '';

            if (item.images && item.images.length > 0) {
                item.images.forEach(img => {
                    const slide = document.createElement('div');
                    slide.className = 'slide';
                    const imageEl = document.createElement('img');
                    
                    let src = img.originalUrl || img.mediumUrl || img.thumbnailUrl;
                    if (src && !src.startsWith('http')) {
                        src = 'https://tapasan.shop/' + src;
                    }

                    imageEl.src = src;
                    imageEl.onerror = function () { this.style.display = 'none'; };
                    slide.appendChild(imageEl);
                    slider.appendChild(slide);
                });
                counter.textContent = `1/${item.images.length}`;
                slider.addEventListener('scroll', () => {
                    const index = Math.round(slider.scrollLeft / slider.offsetWidth);
                    counter.textContent = `${index + 1}/${item.images.length}`;
                });
            }

            document.getElementById('itemPrice').textContent = `${item.price} AZN`;
            document.getElementById('itemTitle').textContent = item.title;
            const dateStr = formatDate(item.createdAt);
            document.getElementById('itemMeta').textContent = `${item.cityName || 'Bakı'}, ${dateStr}`;
            
            if (item.category) {
                document.getElementById('itemCategory').textContent = item.category.name;
            }

            document.getElementById('itemDescription').innerText = item.description;
            document.getElementById('itemCity').textContent = `${item.cityName || 'Bakı'}, Azərbaycan`;
        }

        fetchItemDetails();
    </script>
</body>
</html>
