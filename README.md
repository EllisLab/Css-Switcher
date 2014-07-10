# Css-Switcher

The CSS Switcher plugin is a simple way to allow skinning on a CSS
formatted site.

The tag will look for an incoming `$_GET` (url), `$_POST` (forms), or
`$_COOKIE` (cookies) variable, which the name of can be specified in
the plugin file, to see if a CSS file has been requested. If it has,
then it will be pulled from an array of CSS file locations in the
plugin file and outputted as the `{file}` variable. Also, it will
store the name of that CSS file in a cookie so it can be retrieved
whenever the user visits the site again.

Here is a quick example using a form.

1. You update the `$css` array in the `CSS_Switch` class in `pi.css_switcher.php`:

		var $css = array(
			'1' => "http://example.com/index.php?css=weblog/weblog_css",
			'apples' => "http://example.com/index.php?css=weblog/weblog2_css",
			'oranges' => "http://example.com/index.php?css=weblog/weblog3_css",
			'4' => "http://example.com/index.php?css=weblog/weblog4_css"
		);

2. You put the following in the `<head>` tags of your pages:

		{exp:css_switcher}
			<link rel='stylesheet' type='text/css' media='all' href='{file}' />
			<style type='text/css' media='screen'>@import "{file}";</style>
		{/exp:css_switcher}


3. You set up a form on your front page where the user chooses their
skin and submits the form:

		<form action="index.php" method="post">
			<select name="css_skin" id="css_skin">
				<option value="1">Default</option>
				<option value="apples">Apples</option>
				<option value="oranges">Oranges</option>
				<option value="4">Pineapples</option>
			</select>
		</form>

4. CSS Switcher finds the requested member of the $css array and puts
in the requested CSS file's URL. Then, a cookie is set that will
have that CSS file presented the next time the site is viewed.

## Parameters

The tag also has two optional parameters.

- `setting=""` - Used to change how the CSS files are chosen. Three different possible
settings.
	- `setting="normal"`: Relies on the `$_GET`, `$_POST`, or `$_COOKIE`
variables to choose a member of the array. If neither of those
are found or the one found is not in the array, then it chooses
the default member of the array
	- `setting="random"`: Will randomly choose any member of the
`$css` array in the plugin file.
- `setting="day"`: Will choose a stylesheet depending on the
day of the week. The keys of the array must either be a number
or abbreviation for the day of the week. Possible array keys are:
`1`, `Sun`, `2`, `Mon`, `3`, `Tue`, `4`, `Wed`, `5`, `Thu`, `6`, `Fri`, `7`, `Sat`.
2. `files=""` - Creates the `$css` array on load using the stylesheet URLs listed
in the parameter. Separate each URL with a pipe ("|"). The first
stylesheet will be given an array key of 1, and the rest after will
be incremented by a value of one (2, 3, 4, etc.) by their order.

## Change Log

- 1.1
	- Updated plugin to be 2.0 compatible
