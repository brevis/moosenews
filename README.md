Вариант страницы [Обсудить] для [RCMP.me](http://rcmp.me)

![Moose News](http://1.bp.blogspot.com/-pxxA3dLq6Jc/VP3WWVI691I/AAAAAAAAAmE/IUIsYLQcEqg/s1600/moosenews.png)

### Установка
1. [Скачать zip](https://github.com/brevis/moosenews/archive/master.zip) или клонировать репозиторий, распаковать в `wp-content/plugins`, активировать.
2. `chmod -R 0777 %plugin_dir%/libs`. 
3. Создать новую страницу с контентом `[moosenews]` (shortcode).
4. Для русификации нужно переключить язык WordPress'a любым удобным способом, или переименовать файл `moosenews-ru_RU.mo` из папки `%plugin_dir%/languages` в соответствии с языковыми настройками WordPress'a.
5. На странице `/wp-admin/options.php` можно найти несколько "недокументированных опций" (ctrl+f -> moosenews).

Тестировалось на WordPress 4.1.1 и PHP 5.5.20

### Changelog
10.03.2015: Добавлена возможность редактировать/удалять темы.
