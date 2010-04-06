<?php
/**
 * This view is for rendering the JS for JSSettings.
 * It json_encodes the values needed for the JSSettings library
 */
?>
var JSSettings = function(){

	var REGEX_KEY = "<([a-zA-Z0-9_]+)>";

	var routes = <?php echo json_encode($routes); ?>;
	var config = <?php echo json_encode($config); ?>;
	var properties = <?php echo json_encode($properties); ?>;

	// Useful for me to clone objects
	var clone = function(obj){
		if(obj == null || typeof(obj) != 'object')
			return obj;

		var temp = new obj.constructor();
		for(var key in obj)
			temp[key] = clone(obj[key]);

		return temp;
	}

	return {
		/**
		 * The getter methods should return clones of the values to protect
		 * the properties from being altered... I hope
		 */
		property: {
			"get": function(name) {
				if (properties[name] == undefined)
					throw("Invalid property specified: " + name);

				return clone(properties[name]);
			}
		},
		config: {
			"get": function(name) {
				if (config[name] == undefined)
					throw("Invalid config specified: " + name);

				return clone(config[name]);
			}
		},
		url: {
			/**
			 * Gets the base URL to the application. To include the current protocol,
			 * use TRUE. To specify a protocol, provide the protocol as a string.
			 *
			 * @param   boolean         add index file
			 * @param   boolean|string  add protocol and domain
			 * @return  string
			 */
			base: function(index, protocol) {
				var kohana = JSSettings.property.get("kohana");

				if (protocol == undefined)
				{
					protocol = true;
				}

				if (protocol === true) {
					// Use the current protocol
					protocol = JSSettings.property.get("request").protocol;
				}

				// Start with the configured base URL
				
				base_url = kohana.base_url;

				if (index === true && kohana.index_file !== "") {
					// Add the index file to the URL
					base_url = base_url + kohana.index_file + "/";
				}

				if (typeof protocol === "string") {
					// See if this was a full url
					var check = base_url.indexOf("://");
					if (check !== -1) {
						var section = base_url.substring(check + 3);
						// Remove everything but the path from the URL
						base_url = section.substring(section.indexOf("/"));
					}

					// Add the protocol and domain to the base URL
					base_url = protocol + "://" + location.host + base_url;
				}

				return base_url;
			},
			site: function(uri, protocol) {
				return JSSettings.url.base(true, protocol) + uri;
			}
		},
		route: {
			"get": function(name) {
				if (routes[name] == undefined)
					throw("Invalid route specified: " + name);

				return clone(routes[name]);
			},
			"uri": function(route, params){

				var route = JSSettings.route.get(route);

				if (params != undefined){
					// merge the parameters into defaults for this route
					for (attribute in params){
						route.defaults[attribute] = params[attribute];
					}
				}

				if (route.uri.indexOf("<") === -1 && route.uri.indexOf("(") === -1){
					// This is a static route, no need to replace anything
					return route.uri;
				}

				do {
					var matches = route.uri.match(/\([^()]+\)/g);

					if (matches == null) {
						break;
					}

					// Search for the matched value
					var search = matches[0];

					// Remove the parenthesis from the match as the replace
					var replace = search.substring(1, search.length - 1);
					
					do {
						var regexp = new RegExp(REGEX_KEY, "g");
						var match = regexp.exec(replace);

						if (match == null) {
							break;
						}

						var key = match[0];
						var param = match[1];

						if (route.defaults[param]) {
							// Replace the key with the parameter value
							replace = replace.replace(key, route.defaults[param]);
						} else {
							// This group has missing parameters
							replace = '';
							break;
						}

					} while (match != null)

					// Replace the group in the URI
					route.uri = route.uri.replace(search, replace);

				} while (matches != null);

				do {
					var match = regexp.exec(route.uri);
					
					if (match == null)
					{
						break;
					}

					var key = match[0];
					var param = match[1];

					if (route.defaults[param] == null) {
						// Ungrouped parameters are required
						throw("Required route parameter not passed: " + param);
					}
					
					route.uri = route.uri.replace(key, route.defaults[param]);
				} while (match != null);

				return route.uri;
			},
			"url": function(name, params, protocol) {
				return JSSettings.url.site(JSSettings.route.uri(name, params), protocol)
			}
		}
	}
}();

// ex: alert(JSSettings.route.url('default', {"controller": "main", "action": "index", "id": "5"}));
