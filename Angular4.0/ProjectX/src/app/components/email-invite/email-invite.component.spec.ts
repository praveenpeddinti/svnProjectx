import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EmailInviteComponent } from './email-invite.component';

describe('EmailInviteComponent', () => {
  let component: EmailInviteComponent;
  let fixture: ComponentFixture<EmailInviteComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EmailInviteComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EmailInviteComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should be created', () => {
    expect(component).toBeTruthy();
  });
});
