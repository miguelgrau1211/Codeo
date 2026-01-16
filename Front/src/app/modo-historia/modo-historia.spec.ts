import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ModoHistoria } from './modo-historia';

describe('ModoHistoria', () => {
  let component: ModoHistoria;
  let fixture: ComponentFixture<ModoHistoria>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ModoHistoria]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ModoHistoria);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
