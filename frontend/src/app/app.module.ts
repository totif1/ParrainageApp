import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { ReactiveFormsModule } from '@angular/forms';
import { HttpClientModule } from '@angular/common/http';
import { CommonModule } from '@angular/common';
import {AppComponent} from './app';
import {HomeComponent} from './components/home/home.component';
import {RegistrationComponent} from './components/registration/registration.component';
import {AdminComponent} from './components/admin/admin.component';
import {AppRoutingModule} from './app.routes';


@NgModule({
  declarations: [
    AppComponent,
    HomeComponent,
    RegistrationComponent,
    AdminComponent
  ],
  imports: [
    BrowserModule,
    CommonModule,
    AppRoutingModule,
    ReactiveFormsModule,
    HttpClientModule
  ],
  providers: [],
  bootstrap: [AppComponent]
})
export class AppModule { }
