#Ampersand
##PHP Template Engine

I wanted a way to combine templates, like in other template engines, while being able to use Mustache. Ampersand was born for that reason and currently this is all it does.

### Use:

Templates files will end with .html for now. This will be changed to handle any extension in the future. 

To include other templates in a template use the template tag. As many templates as needed can be listed in the templates tag. As a convenience the template tag can be singular or plural (template or templates) -- it makes no difference either way. 

```

//file: index.html
<& template layout menu home-page-content &>
```
		
A template does not need to list all templates required. For instance, if a layout template requires a header and footer template, the tag can just include the layout template and the layout template will include the header and footer teampltes. 

```

//file: index.html
<& template layout &>

//file: layout.html
<& templates header footer &>

$ampersand->render('index');
```

Templates use a _get_ and _put_ tags to combine the contents. Get tags and be nested within put tags. Put tags cannot be nested. 

```

//file: file-1.html
<& get menu &>
	<p>Default content</p>
<& end menu &>

//file: file-2.html
<& put menu &>
	<ul>
		</li>Home</li>
		</li>About</li>

		<& get login-menu &>

		<& end login-menu &>
	</ul>
<& end menu &>

// file: login-menu.html
<& put login-menu &>
		<li>Login</li>
<& end login-menu &>
```

License: MIT
Copyright &copy; 2015 Jason D. Stidd
