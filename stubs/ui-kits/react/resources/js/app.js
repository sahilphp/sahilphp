import { createElement } from 'react';
import { createRoot } from 'react-dom/client';
import App from './App.jsx';
import '../css/app.css';

const target = document.getElementById('app');

if (target !== null) {
  createRoot(target).render(createElement(App));
}
