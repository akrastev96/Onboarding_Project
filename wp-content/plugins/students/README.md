# DX Plugin Boilerplate

A standardized, organized, object-oriented foundation for building DX Plugins.

## Contents

The DX Plugin Boilerplate includes the following files:

- **.gitignore** - used to exclude certain files from the repository.
- **CHANGELOG.md** - the list of changes to the core project.
- **README.md** - the file that you’re currently reading.
- **dx-plugin-name** - directory that contains the source code

## Quick Links
- [DX Plugin Boilerplate](#dx-plugin-boilerplate)
	- [Contents](#contents)
	- [Quick Links](#quick-links)
	- [Features](#features)
	- [Installation](#installation)
		- [Namespaces](#namespaces)
	- [The 'Loader' class](#the-loader-class)
			- [How to you use the 'Loader' class to hook actions and filters](#how-to-you-use-the-loader-class-to-hook-actions-and-filters)
	- [That's all.](#thats-all)
	- [Licensing](#licensing)

## Features

- The DX Plugin Boilerplate is based on the [Plugin API](http://codex.wordpress.org/Plugin_API), [Coding Standards](https://developer.wordpress.org/coding-standards/), and [Documentation Standards](https://developer.wordpress.org/coding-standards/inline-documentation-standards/php/).
- All classes, functions, and variables are documented so that you know what you need to change.
- The DX Plugin Boilerplate uses a strict file organization scheme and that makes it easy to organize the files that compose the plugin.
- The plugin includes a **'.pot'** file as a starting point for internationalization.

## Installation

The DX Plugin Boilerplate can be installed directly into your plugins folder "as-is". You will want to rename it and the classes inside of it to fit your needs.

Or you can use our [DevriX Scaffold CLI](https://github.com/DevriX/dx-scaffold) tool, which would be the recommended step.

Terms like **'plugin-name'** and other variations are spread all throughout the file contents as well. You can use VS Code, Sublime Text, Atom.io or other capable text editors to mass-replace within multiple files. 

**Note:** *Here is a list of what you should replace (make sure to do case-sensitive search-replaces).*

For example, if your plugin is named **'dx-example-plugin'** then:

- Rename namespace from **PLUGIN_NAME** to **DXEP**
- Rename namespace **plugin_name** in autoloader to **dxep**
```php
spl_autoload_register(
function( $class ) {
	...
	if ( 'plugin_name' === $path_array[0] ) {
	...
});
```		
- Rename main class **class-plugin-name.php** to **class-dx-example-plugin.php**
- Rename path constants **PLUGIN_NAME_DIR** and **PLUGIN_NAME_URL** to **DXEP_DIR** and **DXEP_URL**
- Rename JS and SASS assets names
- **Plugin_Name** should become **DX_Example_plugin**
- **plugin-name** should becode **dx-example-plugin**
- rename **plugin-name.pot** file to **dx-example-plugin.pot**

### Compile the assets
This plugin uses Webpack to process the assets.To compile them you first need to install all packages. Run `npm install`

There are few commands that compile the assets in a different way:
- `npm run watch` - This will compile them for production and will continue monitoring for changes
- `npm run prod` - Compiles for production once and exits
- `npm run prod-bundle` - Compiles for production once and bundles JavaScript files into a single `bundle.js`
- `npm run dev` - Compiles assets in development mode which enables better debugging and watches for changes.

**Note: Assets should be compiled for production before pushing to Git**
## Recommended Tools

### i18n Tools

The DX Plugin Boilerplate uses a variable to store the text domain used when internationalizing strings throughout the Boilerplate. To take advantage of this method, there are tools that are recommended for providing correct, translatable files:

- [Poedit](http://www.poedit.net/)
- [i18n](https://codex.wordpress.org/I18n_for_WordPress_Developers)

Any of the above tools should provide you with the proper tooling to internationalize the plugin.
# Important Notes

## Includes

Note that if you include your own classes, or third-party libraries, there are four locations in which said files may go:

- **dx-plugin-name/includes/functions.php** is where general functions that are too short for a class, etc. of the site reside
- **dx-plugin-name/includes/classes** is where functionality shared between the admin area and the public area parts of the site reside
- **dx-plugin-name/assets/src/admin** is for all admin-specific scripts and styles
- **dx-plugin-name/assets/src/public** is for all public-specific scripts and styles

**Note:** *We have the **Loader** class for registering the hooks.*

### Creating new classes
Our goal is to make the code easy to navigate, understand and maintain. For this to be possible, you should follow few rules.
- Always document your code! It makes a huge difference and can save a lot of time when you or your collegue needs to revisit it. Ff you are not sure how, see this: [Documentation Standards](https://developer.wordpress.org/coding-standards/inline-documentation-standards/php/)
- All of the functionalities related to a single CPT (registering it, taxonomies, shortcodes, etc) should reside in a single class named after the CPT.
- Functions.php should only contain helper functions and small pieces of code. It's goal is to hold generic functions that can be used across the plugin and so we don't have to create a class that only has a single line of code.

***
## Namespaces and Autoloading

### Autoloading
All of the classes that reside in the **dx-plugin-name/includes** folder are autoloaded via PHP's built-in `spl_autoload_register()` and there is no need to manually require or include them.

In order for the classes to be loaded:
- the file needs to be prepended with "**class-**".
- the file needs to be named after the Class inside it.

**Example:**

The following class has to be placed inside "**includes/classes/`class-example`.php**" and have the proper namespace for it to be autoloaded.
```php
namespace PLUGIN_NAME;

class Example {
	public function __constructor() {
		...
	}
}
```

### Namespaces

The plugin comes with namespacing out-of-the-box and every new class needs to be in it.

The autoloader supports sub-namespaces but it requires a specific folder structure.

**Example:**

A class with the following namespace and name would have to be placed inside:

"**includes/classes/`sub-dx`/class-example.php**"
```php
namespace DX/SUB_DX;

class Example {
	public function __constructor() {
		...
	}
}
```


***
## The 'Loader' class

The goal of this class is to encapsulate the registration of hooks and then execute both actions and filters at the appropriate time when the plugin is loaded.

#### How to you use the 'Loader' class to hook actions and filters

In order to understand how to use the loader, we should look at the source for the core plugin class, **Plugin_Name** in **class-plugin-name.php**.

The first block of code in this class declares the private container `$loader`:

```php
protected $loader;
```

Next, we see within the `__construct()` method that the functions `load_dependencies()` and `define_public_hooks()` are called:

```php
public function __construct() {
	$this->plugin_name = 'plugin-name';

	$this->load_dependencies();
	$this->set_locale();
	$this->define_admin_hooks();
	$this->define_public_hooks();
}
```

Following the construct, we see the function `load_dependencies()` defined. Here is where the resource files for classes used by the plugin are autoloaded.

```php
spl_autoload_register(
	function( $class ) {
		$class                = str_replace( '_', '-', strtolower( $class ) );
		$path_array           = explode( '\\', $class );
		$index                = count( $path_array ) - 1;
		$path_array[ $index ] = 'class-' . $path_array[ $index ];
		$class_path           = implode( DIRECTORY_SEPARATOR, $path_array );
		$classpath            = DX_DIR . 'includes/classes' . DIRECTORY_SEPARATOR . $class_path . '.php';

		if ( file_exists( $classpath ) ) {
			include_once $classpath;
		}
	}
	// Run the loader.
	$this->loader = new Plugin_Name_Loader();
);
```

So now, the first step is done.

---

Continuing our examination, we see that at the end of this function, our earlier declared container called `$loader` is now defined as a **new object** of the class **Plugin_Name_Loader**, which it can do now because it was just required earlier in this function:

```php
$this->loader = new Plugin_Name_Loader();
```

Now let's skip down and take a look at `define_public_hooks()`. This is another function that was called from the `__construct()` method earlier. This is a great place to organize hooks used by the plugin. Here's how it looks:

```php
private function define_public_hooks() {
    
	// Instantiates a new object of the class Plugin_Name_Public.
    $plugin_public = new Plugin_Name_Public( $this->get_plugin_name() );
    
	// This is where the loader's add_action() hooks the callback function of the class object. 
    $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
    
	// Another action is passed to the class object.
    $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
}
```

**You can see two important things going on here:**

- An object of a class is instantiated.

- The loader custom version of add_action() is performed which accepts the object as an argument.

This is where the loader becomes useful for us. Instead of passing just the hook and callback function to `add_action()`, the loader uses it's own custom `add_action()` and `add_filter()` functions which allow us to pass three arguments: *hook*, *class*, and *callback* function. This way it knows how to find the function in your class.

For reference, here are [all of the arguments](https://github.com/mnestorov/dx-plugin-boilerplate/blob/main/dx-plugin-name/includes/classes/class-plugin-name-loader.php) accepted by the loader's version of `add_action()`:

```php
public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
	$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
}
```

So now, the second step is done.

---

While there are ways to pass a class function to the WordPress `add_action()` or `add_filter()`, this is how we do it through the boilerplate's loader.

**Example**

Within the function `define_public_hooks()`, we will repeat the two steps we just examined above. 

1. Instantiate an object of our class:

```php
$plugin_cpt = new Plugin_Name_Register_Post_Types();
```

2. Use the loader's `add_action()` to hook a function of `$plugin_cpt`:

```php
$this->loader->add_action( 'init', $plugin_cpt, 'register_business_type' );
```

That's all. 
---

**In retrospect of all of the above, this *Loader* class helps to easily manage hooks throughout the plugin as we're working on our code, and we can trust that everything will be registered with WordPress just as we would expect.**

## Licensing

// @TODO
# Students Plugin

A clean, organized WordPress plugin scaffold built using the DevriX DX Plugin Boilerplate.  
This plugin currently serves as an empty foundation for future development related to managing or extending student‑related functionality.

## Contents

The Students plugin includes the following structure:

- **students.php** – Main plugin bootstrap file.
- **includes/** – Core PHP classes and helper functions.
- **includes/classes/** – Autoloaded classes for public, admin, i18n, activation, deactivation, and shared functionality.
- **assets/** – Source and compiled JavaScript and SCSS assets for both admin and public areas.
- **languages/** – Translation files, including `students.pot`.
- **templates/** – Template files used by the plugin.
- **uninstall.php** – Cleanup logic executed when uninstalling the plugin.

## Features

- Organized, object‑oriented structure based on the DevriX DX Plugin Boilerplate.
- Autoloading of classes through the built‑in `spl_autoload_register()` mechanism.
- Strict folder structure for clarity and maintainability.
- Internationalization support via a `.pot` file.
- Asset pipeline powered by Webpack.

## Installation

1. Copy the plugin folder **students/** into your WordPress installation at  
   `wp-content/plugins/`.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. (Optional) Compile assets if modifying JavaScript or SCSS:

   ```bash
   npm install
   npm run dev
   # or build for production
   npm run prod
   ```

## Namespaces and Autoloading

All plugin classes are namespaced under:

```
namespace Students;
```

Classes inside `includes/classes/` are automatically loaded if:

- The file name is prefixed with `class-`
- The file name matches the class name (kebab‑case)
- The namespace structure matches the folder structure

Example:

A class named:

```
namespace Students;

class Example {}
```

Should be located at:

```
includes/classes/class-example.php
```

## Loader Class

The Loader class is responsible for centralizing WordPress action and filter registrations.

Usage example:

```php
$students_admin = new Students\Admin( $this->get_plugin_name() );
$this->loader->add_action( 'admin_enqueue_scripts', $students_admin, 'enqueue_scripts' );
```

## Asset Compilation

The plugin uses Webpack for compiling JavaScript and SCSS.

Available commands:

- `npm run watch` — Watch for changes and rebuild automatically.
- `npm run prod` — Build for production.
- `npm run prod-bundle` — Build and bundle scripts into `bundle.js`.
- `npm run dev` — Development mode with source maps.

Ensure production assets are compiled before pushing changes.

## Internationalization

Translation files are located in the **languages/** directory.  
The plugin uses the text domain:

```
students
```

You may generate new `.po` or `.mo` files using Poedit or WP‑CLI.

## File Locations Overview

- **students/includes/functions.php** — Helper functions.
- **students/includes/classes/** — All PHP classes.
- **students/assets/src/admin/** — Admin scripts and styles.
- **students/assets/src/public/** — Public‑facing scripts and styles.

## Contributing

Follow WordPress and DevriX coding standards when adding new functionality:

- Proper documentation blocks.
- Single‑responsibility classes.
- Clear and consistent naming.

## Licensing

This plugin inherits the structure of the DevriX DX Plugin Boilerplate and is licensed under GPL‑2.0+.