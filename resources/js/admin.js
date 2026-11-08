// Admin panel: Mazer's Bootstrap, sidebar and dark-mode scripts, plus the section editor
import * as bootstrap from 'bootstrap';
import './admin/globals';
import './admin/sidebar';
import './admin/dark';
import { initEditor } from './admin/editor';

window.bootstrap = bootstrap;
document.addEventListener('DOMContentLoaded', initEditor);
