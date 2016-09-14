# Myanmar Math CAPTCHA
![Example](examples/1.jpg)
![Example](examples/2.jpg)
![Example](examples/3.jpg)
![Example](examples/4.jpg)
![Example](examples/5.jpg)
![Example](examples/6.jpg)
![Example](examples/7.jpg)
![Example](examples/8.jpg)
# Requirements

- PHP 5.4+

# Installation
	
	composer require bodawpaya/mmcaptcha

# Usage

```php
<?php

require 'vendor/autoload.php';

$captcha = (new MyanmarCaptcha\Captcha());
$captcha = $captcha
    ->width(180)
    ->height(50)
    ->fontSize(40)
    ->textColor("#000000")
    ->backgroundColor("#FFFFFF")
    ->backgroundImage("./src/assets/bg1.png")
    ->horizontalLines(5)
    ->disableDistortion()
    ->dots(2000)
    ->verticalLines(20)
    ->invert()
    ->build();

echo $captcha->response('jpg', 100);
```

# Testing

	$ vendor/bin/phpunit

# License

This library is released under the MIT License. See [License](LICENSE) file for more details.
