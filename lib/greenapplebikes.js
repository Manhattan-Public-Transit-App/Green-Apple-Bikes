/**
 * @function NearestBike
 * Function to find the nearest bike to the lat/long provided.
 *
 * @param  {Array}  availableBikes  array of available bikes
 * @param  {Number} latitude        The latitude to find bikes near
 * @param  {type}   longitude       The longitude to find bikes near
 * @return {Bike}   The nearest bike object
 */
function NearestBike(availableBikes, latitude, longitude) {
    // Initialize the minimum distance to a large value
    var mindif = 999999;

    // Initialize the variable to store the closest bike in
    var closest;

    for (i = 0; i < availableBikes.length; i++) {
        // for each bike that is available, check if it is closer than the closest bike found so far.
        var dif = PythagorasEquirectangular(latitude, longitude, availableBikes[i].latitude, availableBikes[i].longitude);
        if (dif < mindif) {
            closest = i;
            mindif = dif;
        }
    }
    return availableBikes[closest];
}

/**
 * @function degreesToRadians
 * Function to convert degrees to radians.
 * Source: http://stackoverflow.com/a/21297385
 *
 * @param {Number} deg
 * @return {Number} The value in radians
 */
function degreesToRadians(deg) {
    return deg * Math.PI / 180;
}

/**
 * @function PythagorasEquirectangular
 * Function to find the Equirectangular projection.
 * Source: http://stackoverflow.com/a/21297385
 *
 * @param {Number} lat1
 * @param {Number} lon1
 * @param {Number} lat2
 * @param {Number} lon2
 * @return {Number} The equirectangular projection.
 */
function PythagorasEquirectangular(lat1, lon1, lat2, lon2) {
    lat1 = degreesToRadians(lat1);
    lat2 = degreesToRadians(lat2);
    lon1 = degreesToRadians(lon1);
    lon2 = degreesToRadians(lon2);
    var R = 6371; // km
    var x = (lon2 - lon1) * Math.cos((lat1 + lat2) / 2);
    var y = (lat2 - lat1);
    var d = Math.sqrt(x * x + y * y) * R;
    return d;
}

/**
 * @function getBikesWithStatus
 * Find bikes with the status requested.
 *
 * @param {string} type the type of bike to request. Currently implemented are "Taken" "Available" and "Might Be Available"
 * @param {string} apiUrl the URL of the API
 * @return {Array} Array of bikes with the status requested.
 */
function getBikesWithStatus(bikeStatus, apiUrl) {
    // Create the request
    var bikeRequest = new XMLHttpRequest();
    bikeRequest.open("GET", apiUrl, false);
    bikeRequest.send(null)

    // Parse the return
    var bikes = JSON.parse(bikeRequest.responseText);

    // Create a variable to store bikes in
    var returnBikes = [];

    // Check each bike to see if it the type requested.
    bikes.forEach(function(bike) {
        if (bike.status == bikeStatus) {
            returnBikes.push(bike);
        }
    });

    return returnBikes;
}


/**
 * @function loadGoogleMapsAPI
 * Loads the Google Maps API
 *
 * @param  {string} googleMapAPIkey The API key from Google.
 */
function loadGoogleMapsAPI(googleMapAPIkey, callback) {
    if (!(typeof google === 'object' && typeof google.maps === 'object')) {
        var googleMapsAPI = document.createElement('script');
        googleMapsAPI.setAttribute('src','https://maps.googleapis.com/maps/api/js?key=' + googleMapAPIkey + "&libraries=places&callback=" + callback);
        document.head.appendChild(googleMapsAPI);
    } else {
        console.log("Just running callback");
        window[callback]();
    }
}
