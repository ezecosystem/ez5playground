A simple extension which overloads the standard "ez_render_field" twig operator to make it easier to create flexible templates:

if an extra parameter is passed to "ez_render_field", it will be used when the desired attribute is not part of
the current content.
This is just a shorthand notation to avoid having IFs all over the place.

Note for eZ4 developers: thanks to the magic of DIC, overoading existing template operators is much nicer now! :-)