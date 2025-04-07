import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import "./assets/custom.scss";
import "bootstrap";
import axios from "axios";

// Font Awesome
import { library } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { fas } from "@fortawesome/free-solid-svg-icons";

library.add(fas);

const app = createApp(App);

app.component("font-awesome-icon", FontAwesomeIcon);
app.use(router);
app.mount("#app");

axios.defaults.baseURL = "http://localhost:8000/api";
