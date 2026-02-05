import { TestBed } from '@angular/core/testing';

import { ProgresoHistoriaService } from './progreso-historia-service';

describe('ProgresoHistoriaService', () => {
  let service: ProgresoHistoriaService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(ProgresoHistoriaService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
