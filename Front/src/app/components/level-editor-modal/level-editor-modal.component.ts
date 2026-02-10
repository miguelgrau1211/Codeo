import { Component, EventEmitter, Input, Output, signal, OnChanges, SimpleChanges } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { StoryLevel, RoguelikeLevel } from '../../services/admin-service';

@Component({
    selector: 'app-level-editor-modal',
    standalone: true,
    imports: [CommonModule, FormsModule],
    templateUrl: './level-editor-modal.component.html',
    styleUrls: ['./level-editor-modal.component.css']
})
export class LevelEditorModalComponent implements OnChanges {
    @Input() isOpen = false;
    @Input() levelType: 'story' | 'roguelike' = 'story';
    @Input() levelData: any = null; // If null, mode is CREATE
    @Output() close = new EventEmitter<void>();
    @Output() save = new EventEmitter<any>();

    // Form Data
    formData: any = {};
    testCases = signal<{ input: string, output: string }[]>([]);

    ngOnChanges(changes: SimpleChanges): void {
        if (changes['isOpen'] && this.isOpen) {
            this.initForm();
        } else if (changes['levelData'] && this.isOpen) {
            this.initForm();
        }
    }

    initForm() {
        if (this.levelData) {
            this.formData = { ...this.levelData };

            let initialTestCases: any[] = [];

            if (this.formData.test_cases) {
                if (typeof this.formData.test_cases === 'string') {
                    try {
                        initialTestCases = JSON.parse(this.formData.test_cases);
                    } catch (e) {
                        console.warn('Error parsing test_cases JSON', e);
                        initialTestCases = [];
                    }
                } else if (Array.isArray(this.formData.test_cases)) {
                    initialTestCases = this.formData.test_cases;
                }
            }

            this.testCases.set(JSON.parse(JSON.stringify(initialTestCases)));

        } else {
            this.testCases.set([]);
            if (this.levelType === 'story') {
                this.formData = {
                    orden: 1,
                    titulo: '',
                    descripcion: '',
                    contenido_teorico: '<h1>Introducción</h1>\n<p>Explica aquí el concepto...</p>',
                    codigo_inicial: '<?php\n\n// Escribe tu código aquí',
                    recompensa_exp: 100,
                    recompensa_monedas: 50
                };
            } else {
                this.formData = {
                    dificultad: 'fácil', // fácil, medio, difícil, extremo
                    titulo: '',
                    descripcion: '',
                    recompensa_monedas: 100
                };
            }
        }
    }

    addTestCase() {
        this.testCases.update(cases => [...cases, { input: '', output: '' }]);
    }

    removeTestCase(index: number) {
        this.testCases.update(cases => cases.filter((_, i) => i !== index));
    }

    submit() {
        // Prepare Payload
        const payload = { ...this.formData };

        // Clean up test cases
        const cleanTestCases = this.testCases().filter(tc => tc.input && tc.output);
        payload.test_cases = cleanTestCases;

        this.save.emit(payload);
    }

    closeModal() {
        this.close.emit();
    }
}
