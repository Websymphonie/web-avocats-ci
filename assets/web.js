import {registerReactControllerComponents} from '@symfony/ux-react';
import './bootstrap.js';
import './styles/tailwind.css';
import './styles/web.scss';

registerReactControllerComponents(require.context('./react/controllers', true, /\.(j|t)sx?$/));
console.log("Web js loaded")