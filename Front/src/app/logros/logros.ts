import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { LogroComponent } from './logro/logro';

@Component({
    selector: 'app-logros',
    standalone: true,
    imports: [CommonModule, LogroComponent],
    templateUrl: './logros.html',
    styleUrls: ['./logros.css']
})
export class LogrosComponent {
   
}