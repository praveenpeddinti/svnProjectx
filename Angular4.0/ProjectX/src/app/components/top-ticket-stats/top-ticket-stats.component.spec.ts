import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { TopTicketStatsComponent } from './top-ticket-stats.component';

describe('TopTicketStatsComponent', () => {
  let component: TopTicketStatsComponent;
  let fixture: ComponentFixture<TopTicketStatsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ TopTicketStatsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(TopTicketStatsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should be created', () => {
    expect(component).toBeTruthy();
  });
});
