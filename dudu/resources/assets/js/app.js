
/**
* First we will load all of this project's JavaScript dependencies which
* includes Vue and other libraries. It is a great starting point when
* building robust, powerful web applications using Vue and Laravel.
*/
import "babel-polyfill";

// Vue
import Vue from "vue";

// Vue-Router
import router from "./routes";    // config file

// Axios
import axios from "axios";
import VueAxios from "vue-axios";

// Vuex
import store from "./store/store";

// Vue style framework Vuetify
import Vuetify from "vuetify";
import "vuetify/dist/vuetify.min.css";

// Global Function
import base from "./base";

// lodash
import lodash from "lodash";

// uploader
import uploader from "vue-simple-uploader";

// wx-js-sdk
import wx from "weixin-js-sdk";
// import wx from "./third-parts/jweixin-1.4.0";

// toast
import toastRegistry from "./toast/index";
Vue.prototype.$toast = toastRegistry;

// swiper
import VueAwesomeSwiper from "vue-awesome-swiper";
import "swiper/dist/css/swiper.css";

// v-charts
import VCharts from "v-charts";

// axios
import api from "./api";
Vue.prototype.api = api;

// 富文本
import Quill from "quill";
import VueQuillEditor from "vue-quill-editor";

// require styles
import "quill/dist/quill.core.css";
import "quill/dist/quill.snow.css";
import "quill/dist/quill.bubble.css";

import ImageResize from "quill-image-resize-module";
Quill.register("modules/imageResize", ImageResize);

// 图片放大插件
import preview from "vue-photo-preview";
import "vue-photo-preview/dist/skin.css";


// Vue use all modules
window.Vue = Vue;
window._ = lodash;
window.wx = wx;
window.host = `http://${window.location.host}`;
Vue.use(VueAxios,axios);
Vue.use(VCharts);
Vue.use(Vuetify);

Vue.use(preview);
Vue.use(base);

Vue.use(VueQuillEditor);
// Vue.use(toastRegistry);
Vue.use(uploader);
Vue.use(VueAwesomeSwiper);

/**
* Next, we will create a fresh Vue application instance and attach it to
* the page. Then, you may begin adding components to this application
* or customize the JavaScript scaffolding to fit your unique needs.
*/
Vue.component("myUser", require("./components/Commons/MyUser"));

/* exported app to be ignored by eslint */
const appIgnored = new Vue({
	el: "#app",
	router,
	store
});

