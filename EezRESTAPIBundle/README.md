Eez REST API Bundle
===================

An extension for eZPublish 5, implementing a REST-ish, extra-simple-to-use API.

What it does
------------

- It implements a json view of content (symfony controller), which can be used, swiss-army-knife-style, to implement
  more or less a whole mobile/ajax site without having to write 1 line of code to access the repository
  (NB: the "give me content to display" part only)

- This controller can load data for a location, its object, children, previous and next sibling, and filter/transform
  the results before sending them back.
  This means it can be optimized for mobile apps, where you want to reduce both the number of http calls and response size

- The way it works is that developers define "views", which can then be accessed at url /getContent/<locationId>/<viewname>

- Each view defines
  * what to fetch from the db (node data, content data, children data etc)
  * which part of this data to send back in the response, possibly filtering and transforming it in the process

- The developer can also define "courtesy" urls, to avoid having to communicate location ids to the clients, eg:
  url /blogposts => mapped into /getContent/59/blogpostsview

- For optimal speed, the native http-cache mechanisms of eZPublish 5 are adopted: X-LocationId and UserHash

- Policy checking is implemented natively by the eZ Repository.
  No content will be shown to the user unless he can access it by policy

Important Notes
---------------

- This extension was designed for getting s**t done. Quickly.

  It does not care in the least about actual REST principles - think of it as JSON-OVER-HTTP.
  To add insult to injury, it does not follow the eZ5 REST API v2 way of doing things.

- This extension does not support xml output. It does not care about "accept" http headers

- If you can not get over its unholiness, just delete it and use whatever else suits you better :-)

How to use the bundle
---------------------
- install it and activate it (load it in your appkernel php file)
- import the bundle routes: in ezpublish/routing.yml, add:

        EezRESTAPIBundle_rest_routes:
            resource: "@GGGeekEZ5EezRESTAPIBundle/Resources/config/routing.yml"
            prefix:   %eezrestapi.prefix%

- if not in dev mode, clear Symfony caches
- head on to /api/eezrest/V1/getContent/2/full
- start developing your custom views, taking example from Rest/Views/Full.php.
  To register your custom view with this bundle, you have to defined it as a tagged service.
  Look at Resources/config/services.yml for an example on how this is done for the "full" view

Todo
----
- improve docs
- create class constants for things which can be fetched
- allow to also load
  + parent
  + children with objects
  + siblings with objects
- add some sample filtering view which uses jquery-like libs to transform the data
- add a controller which lists available views (ateoas?)
