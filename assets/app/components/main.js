import EventService from "../services/event.service.js";
import RouterService from "../services/router.service.js";
import Component from "./component.js";

const template = `
   <app-header>
        JUST-TEXT
        <li>{{data.title}}</li>
    </app-header>

    <nav>
        <div class="link" navigate="/">HOME</div>
        <div class="link" navigate="/about">/about</div>
        <div class="link" navigate="/about/">/about/</div>
        <div class="link" navigate="/about/log">/about/log</div>
        <div class="link" navigate="/about/test">/about/test</div>
        <div class="link" navigate="/about/88">/about/88</div>
        <div class="link" navigate="/no-route">NO-ROUTE</div>
    </nav>

    <app-router>
        <div class="page" route="/" activeClass="active">DIV<br>/</div>
        <app-home class="page" route="/about" activeClass="active">
            <div>/about</div>
             <app-router parentRoute="/about">
                <app-home class="page" route="/" activeClass="active-2">/</app-home>
                <app-home class="page" route="/log" activeClass="active-2">/log</app-home>
            </app-router>
        </app-home>
        <app-home class="page" route="/about/test" activeClass="active">/about/test</app-home>
        <app-home class="page" route="/about/{id}" activeClass="active">/about/{id}</app-home>
    </app-router>

    

    {{#if data.titles}}
        <div>IF BLOCK</div>
    {{/if}}
    
    {{#each people}}
        
    {{/each}}

    <app-footer>
        {{data.title}}
    </app-footer>
`;

export default class Main extends Component {

    tagName = "app-main";
    html = template;

    data = {
        title: "HELLO"
    }

    constructor(events = EventService, routerService = RouterService) {
        super();
        this.events = events;
        this.routerService = routerService;
    }

    render() {
        super.render();

        this.querySelector("nav").addEventListener("click", (event) => {
            let route = event.target.getAttribute("navigate");
            if (route) {
                this.routerService.navigate(route);
            }
        })
    }
}
