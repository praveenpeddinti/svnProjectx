import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ChildtaskComponent } from './childtask.component';

describe('ChildtaskComponent', () => {
  let component: ChildtaskComponent;
  let fixture: ComponentFixture<ChildtaskComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ChildtaskComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ChildtaskComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should be created', () => {
    expect(component).toBeTruthy();
  });
});
