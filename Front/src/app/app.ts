import { Component, signal } from '@angular/core';
import { RouterOutlet, RouterLink } from '@angular/router';
import { Header } from './header/header';
import { AchievementNotificationComponent } from './components/achievement-notification/achievement-notification';

@Component({
  selector: 'app-root',
  imports: [RouterOutlet, RouterLink, Header, AchievementNotificationComponent],
  templateUrl: './app.html',
  styleUrl: './app.css'
})
export class App {
  protected readonly title = signal('Codeo');
}
