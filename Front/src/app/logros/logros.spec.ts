import { ComponentFixture, TestBed } from '@angular/core/testing';
import { LogrosComponent } from './logros';


describe('LogrosComponent', () => {
  let component: LogrosComponent;
  let fixture: ComponentFixture<LogrosComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [LogrosComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(LogrosComponent);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
