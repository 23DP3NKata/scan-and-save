import { createApp } from "vue";
import { createPinia } from "pinia";

import App from "./App.vue";
import router from "./router";
import vuetify from "./plugins/vuetify";
import { installLatvianValidation } from "./services/latvianValidation";

const app = createApp(App);
installLatvianValidation();

app.use(createPinia());
app.use(vuetify);
app.use(router);

app.mount("#app");
