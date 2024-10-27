<div style="text-align: center;">
    <img src="https://upload.wikimedia.org/wikipedia/commons/d/dd/Papara_Logo.png" alt="Ödeme Sayfası" width="250px" height="83px">
</div>

# WiseCP İçin Papara Ödeme Modülü 3D

Bu modül, Papara Ödeme Ağ Geçidi'ni WiseCP platformuna entegre eder. 
Kullanıcıların Papara'nın güvenli API'si aracılığıyla ödeme yapmasına olanak tanır. Modül, temel ödeme işlemleri, komisyon hesaplamaları, ve otomatik çağrı geri döndürme gibi özellikler sunar.

## Ekran Görüntüleri

### 1. Modül Yapılandırma Ekranı
<img src="https://raw.githubusercontent.com/OmerAti/wisecp-papara/main/images/papara1.png" alt="Yapılandırma Ekranı">

### 2. Ödeme Sayfası
<img src="https://raw.githubusercontent.com/OmerAti/wisecp-papara/main/images/papara2.png" alt="Ödeme Sayfası">


## Özellikler

- **Güvenli Ödeme İşleme:** Papara'nın API'sini kullanarak güvenli ödeme işlemleri gerçekleştirir.
- **Komisyon Hesaplama:** İşlem tutarından otomatik olarak komisyon hesaplar ve düşer.
- **API Kimlik Bilgilerini Yapılandırma:** WiseCP yönetim paneli üzerinden API URL'si ve anahtarlarını kolayca yapılandırın.
- **Otomatik Callback:** Ödeme işlemi tamamlandığında, sonuçlar otomatik olarak geri çağrılır ve sistemde işlenir.
- **Ülke Tabanlı Gösterim:** Modül, belirli ülkelerde kullanılabilir veya sınırlandırılabilir.

## Kurulum

1. **Modülü İndir:**
   - Depoyu klonlayın veya zip dosyasını indirin.
   - Zipten çıkarın
   - Klasor ismini Papara yapın
   - coremio/modules/Payment klasorune atın
   - WiseCP paneline girip Ayarlar/Finansal Ayarlar/ Ödeme Yöntemlerine gidin
   - Papara Ödeme Yöntemini Seçip Yapılandırın
   - 
## Kullanım
   -  Modül kurulduktan ve yapılandırıldıktan sonra:
   -  Müşteriler, ödeme sayfasında Papara'yı bir ödeme seçeneği olarak görecekler.
   -  Seçtiklerinde, ödeme bilgilerini girmeleri istenecektir.
   -  İşlem Yönetimi:

   -  Modül, ödeme bilgilerini Papara'nın API'sine iletecek.
   -  Yanıta göre müşteri, başarı veya başarısızlık sayfasına yönlendirilecektir.
