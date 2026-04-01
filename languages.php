<?php
$lang_code = isset($_COOKIE['site_lang']) ? $_COOKIE['site_lang'] : 'en';

// Fallback if cookie has invalid value
if (!in_array($lang_code, ['en', 'ms', 'zh', 'th', 'ta'])) {
    $lang_code = 'en';
}

$all_translations = [
    'en' => [
        'home' => 'Home', 'menu' => 'Menu', 'about' => 'About', 'contact' => 'Contact', 'login' => 'Login', 'profile' => 'Profile',
        'hero_title' => 'Your Burger, Your Way', 'customize' => 'Customize Your Burger',
        'about_title' => 'About Bambam Burger',
        'contact_title' => 'Bambam Burger Branches',
        'login_title' => 'LOGIN', 'signup_title' => 'Sign Up',
        'my_profile' => 'My Profile', 'edit_profile' => 'Edit Profile', 'favourites' => 'Favourites', 'language' => 'Language', 'location' => 'Location', 'history' => 'Purchase History',
        'branch' => 'Branch', 'payment' => 'Payment Method',
        'order_type' => 'Order Type', 'take_away' => 'Take-Away', 'drive_thru' => 'Drive-Thru',
        'upload_receipt' => 'Upload Payment Receipt', 'bank_details' => 'Bank Details',
        'pay_counter' => 'PAY IN CASH, PLEASE PAY AT THE COUNTER',
        'thank_you' => 'Thank you for visiting', 'customer_service' => 'Customer Service',
        'sender_name' => 'Sender Name', 'receipt_preview' => 'Receipt Preview',
        'confirm_authentic' => 'I confirm this is an actual receipt',
        'order_number' => 'Order Number',
        'footer_text' => '© 2026 Bambam Burger | Follow us on social media'
    ],
    'ms' => [
        'home' => 'Utama', 'menu' => 'Menu', 'about' => 'Tentang Kami', 'contact' => 'Hubungi', 'login' => 'Log Masuk', 'profile' => 'Profil',
        'hero_title' => 'Burger Anda, Cara Anda', 'customize' => 'Ubah Suai Burger',
        'about_title' => 'Tentang Bambam Burger',
        'contact_title' => 'Cawangan Bambam Burger',
        'login_title' => 'LOG MASUK', 'signup_title' => 'Daftar Masuk',
        'my_profile' => 'Profil Saya', 'edit_profile' => 'Sunting Profil', 'favourites' => 'Kegemaran', 'language' => 'Bahasa', 'location' => 'Lokasi', 'history' => 'Sejarah Pembelian',
        'branch' => 'Cawangan', 'payment' => 'Kaedah Pembayaran',
        'order_type' => 'Jenis Pesanan', 'take_away' => 'Bungkus', 'drive_thru' => 'Pandu Lalu',
        'upload_receipt' => 'Muat Naik Resit Pembayaran', 'bank_details' => 'Butiran Bank',
        'pay_counter' => 'BAYAR TUNAI, SILA BAYAR DI KAUNTER',
        'thank_you' => 'Terima kasih kerana berkunjung', 'customer_service' => 'Khidmat Pelanggan',
        'sender_name' => 'Nama Pengirim', 'receipt_preview' => 'Pratonton Resit',
        'confirm_authentic' => 'Saya mengesahkan ini adalah resit sebenar',
        'order_number' => 'Nombor Pesanan',
        'footer_text' => '© 2026 Bambam Burger | Ikuti kami di media sosial'
    ],
    'zh' => [
        'home' => '首页', 'menu' => '菜单', 'about' => '关于', 'contact' => '联系', 'login' => '登录', 'profile' => '个人资料',
        'hero_title' => '您的汉堡，您的选择', 'customize' => '定制您的汉堡',
        'about_title' => '关于 Bambam Burger',
        'contact_title' => 'Bambam Burger 分店',
        'login_title' => '登录', 'signup_title' => '注册',
        'my_profile' => '我的资料', 'edit_profile' => '编辑资料', 'favourites' => '收藏', 'language' => '语言', 'location' => '位置', 'history' => '购买记录',
        'branch' => '分店', 'payment' => '付款方式',
        'order_type' => '订单类型', 'take_away' => '外带', 'drive_thru' => '得来速',
        'upload_receipt' => '上传付款收据', 'bank_details' => '银行详情',
        'pay_counter' => '现金支付，请在柜台付款',
        'thank_you' => '感谢您的光临', 'customer_service' => '客户服务',
        'sender_name' => '汇款人姓名', 'receipt_preview' => '收据预览',
        'confirm_authentic' => '我确认这是真实收据',
        'order_number' => '订单号码',
        'footer_text' => '© 2026 Bambam Burger | 关注我们的社交媒体'
    ],
    'th' => [
        'home' => 'หน้าหลัก', 'menu' => 'เมนู', 'about' => 'เกี่ยวกับ', 'contact' => 'ติดต่อ', 'login' => 'เข้าสู่ระบบ', 'profile' => 'โปรไฟล์',
        'hero_title' => 'เบอร์เกอร์ของคุณ ในแบบของคุณ', 'customize' => 'ปรับแต่งเบอร์เกอร์',
        'about_title' => 'เกี่ยวกับ Bambam Burger',
        'contact_title' => 'สาขา Bambam Burger',
        'login_title' => 'เข้าสู่ระบบ', 'signup_title' => 'ลงชื่อเข้าใช้',
        'my_profile' => 'โปรไฟล์ของฉัน', 'edit_profile' => 'แก้ไขโปรไฟล์', 'favourites' => 'รายการโปรด', 'language' => 'ภาษา', 'location' => 'ตำแหน่ง', 'history' => 'ประวัติการซื้อ',
        'branch' => 'สาขา', 'payment' => 'วิธีการชำระเงิน',
        'order_type' => 'ประเภทการสั่งซื้อ', 'take_away' => 'กลับบ้าน', 'drive_thru' => 'ไดร์ฟทรู',
        'upload_receipt' => 'อัปโหลดใบเสร็จการชำระเงิน', 'bank_details' => 'รายละเอียดธนาคาร',
        'pay_counter' => 'ชำระเงินสด กรุณาชำระที่เคาน์เตอร์',
        'thank_you' => 'ขอบคุณที่มาใช้บริการ', 'customer_service' => 'บริการลูกค้า',
        'sender_name' => 'ชื่อผู้ส่ง', 'receipt_preview' => 'ตัวอย่างใบเสร็จ',
        'confirm_authentic' => 'ฉันยืนยันว่านี่คือใบเสร็จจริง',
        'order_number' => 'หมายเลขคำสั่งซื้อ',
        'footer_text' => '© 2026 Bambam Burger | ติดตามเราบนโซเชียลมีเดีย'
    ],
    'ta' => [
        'home' => 'முகப்பு', 'menu' => 'மெனு', 'about' => 'பற்றி', 'contact' => 'தொடர்பு', 'login' => 'உள்நுழை', 'profile' => 'சுயவிவரம்',
        'hero_title' => 'உங்கள் பர்கர், உங்கள் வழி', 'customize' => 'உங்கள் பர்கரைத் தனிப்பயனாக்கவும்',
        'about_title' => 'Bambam Burger பற்றி',
        'contact_title' => 'Bambam Burger கிளைகள்',
        'login_title' => 'உள்நுழை', 'signup_title' => 'பதிவு',
        'my_profile' => 'என் சுயவிவரம்', 'edit_profile' => 'சுயவிவரத்தைத் திருத்து', 'favourites' => 'விருப்பங்கள்', 'language' => 'மொழி', 'location' => 'இடம்', 'history' => 'கொள்முதல் வரலாறு',
        'branch' => 'கிளை', 'payment' => 'கட்டண முறை',
        'order_type' => 'ஆர்டர் வகை', 'take_away' => 'எடுத்துச் செல்லுதல்', 'drive_thru' => 'டிரைவ்-த்ரூ',
        'upload_receipt' => 'கட்டண ரசீதைப் பதிவேற்றவும்', 'bank_details' => 'வங்கி விவரங்கள்',
        'pay_counter' => 'பணமாகச் செலுத்துங்கள், கவுண்டரில் செலுத்துங்கள்',
        'thank_you' => 'வருகைக்கு நன்றி', 'customer_service' => 'வாடிக்கையாளர் சேவை',
        'sender_name' => 'அனுப்புநர் பெயர்', 'receipt_preview' => 'ரசீது முன்னோட்டம்',
        'confirm_authentic' => 'இது உண்மையான ரசீது என்பதை உறுதிப்படுத்துகிறேன்',
        'order_number' => 'ஆர்டர் எண்',
        'footer_text' => '© 2026 Bambam Burger | சமூக ஊடகங்களில் எங்களைப் பின்தொடரவும்'
    ]
];

$t = $all_translations[$lang_code];
?>