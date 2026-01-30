import { Component, signal } from '@angular/core';
import { RouterOutlet, RouterLink } from '@angular/router';
import { Header } from './header/header';

@Component({
  selector: 'app-root',
  imports: [RouterOutlet, RouterLink, Header],
  templateUrl: './app.html',
  styleUrl: './app.css'
})
export class App {
  protected readonly title = signal('Codeo');
}
