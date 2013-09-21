wp_plurk - 發表文章後同步到噗浪
========

使用方法
========

1. 到 http://www.plurk.com/PlurkApp/ 申請一個 Plurk App (選 Create a new Plurk App)
  1. App Name 請自由取名
  2. Organization, Description 自由填寫
  2. Website 填寫自己的 Blog
  3. callback 留空
  填寫好之後如下圖:
  ![new app](https://raw.github.com/jnlin/wp_plurk/master/images/apply.png)
2. 按下 Register App，會出現新的 App。接著選 "test console"
  ![api](https://raw.github.com/jnlin/wp_plurk/master/images/api.png)
3. 依序按下 "Get request token", "Open Authorization URL", "Get Access Token"
  ![key](https://raw.github.com/jnlin/wp_plurk/master/images/key.png)
  1. 按下 "Open Authorization URL" 之後，會出現授權畫面，選擇 Yes
  ![auth](https://raw.github.com/jnlin/wp_plurk/master/images/auth.png)
  2. 接著會出現授權碼（六位數字），將它複製起來
  ![authcode](https://raw.github.com/jnlin/wp_plurk/master/images/authcode.png)
  3. 接著回到 test console，按下 "Get Access Token"，並輸入剛剛的授權碼
  ![doauth](https://raw.github.com/jnlin/wp_plurk/master/images/doauth.png)
4. 接著把 Consumer Key, Consumer Secret, Token Key, Token Secret 複製到 Plurk Plugin 設定頁面，存檔後即可開始使用
  ![plugin](https://raw.github.com/jnlin/wp_plurk/master/images/plugin.png)
