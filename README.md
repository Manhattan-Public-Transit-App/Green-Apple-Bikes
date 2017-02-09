# Green Apple Bikes Web Application

### Setup

##### Prerequisites
First, install [node.js](https://nodejs.org/en/). Follow the instructions at the link.

Next, install the [Polymer CLI](https://github.com/Polymer/polymer-cli) using
[npm](https://www.npmjs.com) (npm is included with node.js).

    npm install -g polymer-cli

Now, install [bower](https://bower.io/) using npm.

    npm install -g bower

#### Clone the Repository
You are now ready to clone the repository. For more information on cloning a repository, refer to [GitHub's documentation](https://help.github.com/articles/cloning-a-repository/).

Now, navigate to the directory where the project is cloned. You will now install the bower packages required by the project. These are listed in a [bower.json](https://bower.io/docs/creating-packages/#bowerjson) file.

    bower install

You have now set up the project.

### Start the development server

This command serves the project at `http://localhost:8080` and provides basic URL
routing for the project:

    polymer serve --open

### Build

This command performs HTML, CSS, and JS minification on the project
dependencies, and generates a service-worker.js file with code to pre-cache the
dependencies based on the entrypoint and fragments specified in `polymer.json`.
The minified files are output to the `build/unbundled` folder, and are suitable
for serving from a HTTP/2+Push compatible server.

In addition the command also creates a fallback `build/bundled` folder,
generated using fragment bundling, suitable for serving from non
H2/push-compatible servers or to clients that do not support H2/Push.

    polymer build

### Preview the build

This command serves the minified version of the project at `http://localhost:8080`
in an unbundled state, as it would be served by a push-compatible server:

    polymer serve build/unbundled

This command serves the minified version of the project at `http://localhost:8080`
generated using fragment bundling:

    polymer serve build/bundled

### Run tests

This command will run [Web Component Tester](https://github.com/Polymer/web-component-tester)
against the browsers currently installed on your machine:

    polymer test
