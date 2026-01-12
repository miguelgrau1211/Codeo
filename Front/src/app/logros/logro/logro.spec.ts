import { ComponentFixture, TestBed } from '@angular/core/testing';

import { LogroComponent } from './logro';

describe('LogroComponent', () => {
  let component: LogroComponent;
  let fixture: ComponentFixture<LogroComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [LogroComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(LogroComponent);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
