# yii2-gii
Генерация кода для проектов, основанных на ExtPoint Yii2 Boilerplate

## Установка

1. Установите пакет через Composer:
```bash
composer require extpoint/yii2-gii
```

2. Добавьте модуль в конфигурацию приложения:

```php
<?php

use extpoint\yii2\components\ModuleLoader;

ModuleLoader::add('extpoint\yii2\gii\GiiModule'); // <--

return [
    'id' => 'my-project',
    // ...
];
```

3. Добавьте в webpack секцию поиска виджета:

```js
require('extpoint-yii2/webpack')
    .base(/* ... */)
    // ...
    
    .widgets('./vendor/extpoint/yii2-gii/lib/widgets') // <--
```