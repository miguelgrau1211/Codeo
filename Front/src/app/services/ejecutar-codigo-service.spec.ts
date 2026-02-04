import { TestBed } from '@angular/core/testing';

import { EjecutarCodigoService } from './ejecutar-codigo-service';

describe('EjecutarCodigoService', () => {
  let service: EjecutarCodigoService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(EjecutarCodigoService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
