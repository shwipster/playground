
import Component from './component.js';
import Events from '../services/event.service.js';
import RouterService from '../services/router.service.js';

const template = `<ng-content></ng-content>`;

export default class Router extends Component {
    tagName = "app-router";
    html = template;

    constructor(routerService = RouterService) {
        super();
        this.routerService = routerService;
    }

    connectedCallback() {

        //console.log(this.getAttributeNames());
        super.connectedCallback();

        let parentRoute = this.getAttribute("parentRoute");
        for (let childNode of this.children) {
            // console.log();
            let route = childNode.getAttribute("route");
            if (route) {
                this.routerService.register(parentRoute, route, this.routeMatch.bind(this));
            }
        }

        if (this.children) {
            let activeClass = this.children[0].getAttribute("activeClass");
            if (activeClass) {
                this.children[0].classList.add(activeClass);
            }
        }

        this.routerService.matchRoutes();
    }

    routeMatch(route) {
        //console.log("match 2");

        for (let childNode of this.children) {
            let activeClass = childNode.getAttribute("activeClass");

            childNode.classList.remove(activeClass);
            if (childNode.getAttribute("route") == route) {
                childNode.classList.add(activeClass);
            }
        }
    }
}