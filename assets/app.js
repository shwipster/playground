/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './app/styles/app.css';
import Header from './app/components/header.js';
import Footer from './app/components/footer.js';
import Router from './app/components/router.js';
import Home from './app/components/home.js';
import About from './app/components/about.js';
import Main from './app/components/main.js';


customElements.define("app-main", Main, {});
customElements.define("app-router", Router, {});
customElements.define("app-header", Header, {});
customElements.define("app-footer", Footer, {});
customElements.define("app-home", Home, {});
customElements.define("app-about", About, {});