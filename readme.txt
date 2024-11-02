=== Chili Code Highlighter ===
Contributors: Viper007Bond
Donate link: http://www.viper007bond.com/donate/
Tags: code hightlight syntax jquery
Requires at least: 2.5
Stable tag: trunk

Allows for easy posting of code and syntax highlights it via a jQuery plugin called Chili by Andrea Ercolino.

== Description ==

Allows for easy posting of code and syntax highlights it via a jQuery plugin called [Chili](http://noteslog.com/chili/) by [Andrea Ercolino](http://noteslog.com/).

**THIS PLUGIN IS STILL IN ALPHA/BETA AND IS A WORK IN PROGRESS. PLEASE DON'T DOWNLOAD IT UNLESS YOU ARE COMFORTABLE WITH THIS.**

A complete version will be coming soon. Please [report any bugs or feedback to me](http://www.viper007bond.com/contact/).

__**USAGE**__

`[code="language"]some code[/code]`

Example:

`[code="php]<?php echo 'Hello world!'; ?>[/code]`

== Installation ==

###Updgrading From A Previous Version###

To upgrade from a previous version of this plugin, delete the entire folder and files from the previous version of the plugin and then follow the installation instructions below.

###Installing The Plugin###

Extract all files from the ZIP file, making sure to keep the file structure intact, and then upload it to `/wp-content/plugins/`.

This should result in the following file structure:

`- wp-content
    - plugins
        - chili-code-highlighter
            | chili-code-highlighter.php
            | readme.txt
            - chili
                | cplusplus.js
                | csharp.js
                | [...]
                | php-f.js
                | recipes.js`

Then just visit your admin area and activate the plugin.

**See Also:** ["Installing Plugins" article on the WP Codex](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins)