import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
    selector: 'app-logro',
    standalone: true,
    imports: [CommonModule],
    templateUrl: './logro.html',
    styles: []
})
export class LogroComponent {
    @Input() isLocked: boolean = false;
    @Input() icon: string = '';
    @Input() title: string = '';
    @Input() rarity: string = 'common';
    @Input() description: string = '';
    @Input() currentProgress: number = 0;
    @Input() maxProgress: number = 0;
    @Input() points: number = 0;
}