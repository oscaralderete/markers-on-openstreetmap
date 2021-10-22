# markers-on-openstreetmap
New WordPress plugin to render a map with multiple markers using the open source maps provider OpenStreetMap

A friend of mine ask me to install my [MultiMarkersOnGmap](https://github.com/oscaralderete/multiple-markers-on-google-maps) on hist site, but he hasn't a key or any knowledgement about how to get a Google Maps key, so the only solution was modify that plugin and use the great and free OpenStreetMap API alternative.

The plugin works fine, it has an admin zone that uses JavaScript reactivity wich is cool and modern (powered by my fav JS library: VueJS). No reloads, no page blinks; but the most important thing, no keys, no fees; thanks to the nice people that make possible OpenStreetMap exists.

The only thing you need to run this awesome plugin in your own WordPress site is, besides install it, to add a shortcode wherever you want and define some markers:
```bash
[MarkersOnOpenStreetMap]
```

Enjoy it!

(PS: As I mentioned before, this is a clone of https://github.com/oscaralderete/multiple-markers-on-google-maps, so if you find any bug, please, let me know to fix it. Apparently work fine, but who knows!)
