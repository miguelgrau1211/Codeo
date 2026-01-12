import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ModoInfinito } from './modo-infinito';

describe('ModoInfinito', () => {
  let component: ModoInfinito;
  let fixture: ComponentFixture<ModoInfinito>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ModoInfinito]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ModoInfinito);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
