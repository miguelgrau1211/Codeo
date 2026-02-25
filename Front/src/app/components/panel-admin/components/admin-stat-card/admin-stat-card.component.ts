import { Component, input } from '@angular/core';

@Component({
  selector: 'app-admin-stat-card',
  standalone: true,
  imports: [],
  templateUrl: './admin-stat-card.component.html',
  styleUrl: './admin-stat-card.component.css',
})
export class AdminStatCardComponent {
  title = input.required<string>();
  value = input.required<string | number>();
  icon = input<string>(''); // e.g., 'bi-people'
  trend = input<string>(''); // e.g., '+5% vs last week'
  trendColor = input<'text-green-500' | 'text-red-500' | 'text-gray-500'>('text-gray-500');
}





