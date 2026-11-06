import { createApp } from 'vue';
import App from './App.vue';
import '../css/app.css';

const target = document.getElementById('app');

if (target !== null) {
  createApp(App).mount(target);
}
