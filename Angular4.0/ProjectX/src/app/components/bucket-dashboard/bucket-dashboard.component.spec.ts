import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { BucketDashboardComponent } from './bucket-dashboard.component';

describe('BucketDashboardComponent', () => {
  let component: BucketDashboardComponent;
  let fixture: ComponentFixture<BucketDashboardComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ BucketDashboardComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(BucketDashboardComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should be created', () => {
    expect(component).toBeTruthy();
  });
});
