
class Router {
    constructor() {
        this.routes = {};

        window.onpopstate = () => {
            this.matchRoutes();
        }
    }

    navigate(route) {
        //console.log(route);
        window.history.pushState({}, '', route);
        this.matchRoutes();
    }

    register(parentRoute, route, callback) {

        let fullpath = route;
        if (parentRoute) {
            fullpath = parentRoute + route;
        }

        this.routes[fullpath] = { parentRoute: parentRoute, route: route, callback: callback };
    }

    matchRoutes() {

        //console.log(this.routes);

        let path = window.location.pathname;

        //Add slash to end so that "index" page in sub routes gets also activated
        if (path[path.length - 1] != "/") {
            path = path + "/";
        }
        this.findFirstMatch(path);
    }

    findFirstMatch(urlpath) {

        // End recursive when no path left
        if (!urlpath) {
            return;
        }

        for (let fullpath in this.routes) {

            let route = this.routes[fullpath];

            let regex = "^" + fullpath.replace(/{.*}/, "\\d*") + "$";
            //console.log(urlpath, fullpath, regex);
            if (urlpath.match(regex)) {
                //console.log("match1");
                route.callback(route.route);
                return this.findFirstMatch(route.parentRoute);
            }
        }

        // If exact route not found then try subpaths
        urlpath = urlpath.split("/")
        urlpath.pop()
        urlpath = urlpath.join("/");

        return this.findFirstMatch(urlpath);

    }
}

let RouterService = new Router();
export default RouterService;