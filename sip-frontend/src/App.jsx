import React, { useEffect } from "react";
import { Routes, Route, useLocation } from "react-router-dom";

import "./css/style.css";

// Import pages

import {
  Activities,
  NewsLanding,
  Daftar,
  Landing,
  Login,
  OpenSingleBerita,
  SemuaBerita,
  SharedLayoutLanding,
} from "./landingPages/";

import {
  DashboardKegiatan,
  DashboardKeluarga,
  Editor,
  DashboardAnak,
  DashboardBerita,
  ChatBot,
} from "./adminPages";

import {
  DashboardAnakUser,
  DashboardBeritaUser,
  DashboardKegiatanUser,
  DashboardUser,
  Profiles,
  SingleBerita,
} from "./userPages";

import { Error, ProtectedRoute, SharedLayout } from "./components";

function App() {
  const location = useLocation();

  useEffect(() => {
    document.querySelector("html").style.scrollBehavior = "auto";
    window.scroll({ top: 0 });
    document.querySelector("html").style.scrollBehavior = "";
  }, [location.pathname]); // triggered on route change

  return (
    <>
      <Routes>
        {/* Dashboard */}
        <Route
          path="/dashboard"
          element={
            <ProtectedRoute>
              <SharedLayout />
            </ProtectedRoute>
          }
        >
          <Route index path="menu" element={<DashboardUser />} />

          <Route path="berita" element={<DashboardBeritaUser />} />
          <Route path="kegiatan" element={<DashboardKegiatanUser />} />
          <Route path="anak" element={<DashboardAnakUser />} />
          <Route path="profile" element={<Profiles />} />

          <Route path="berita/:id" element={<SingleBerita />} />

          {/* Admin */}
          <Route path="dataanak" element={<DashboardAnak />} />
          <Route path="datakegiatan" element={<DashboardKegiatan />} />
          {/* <Route path="datakeluarga" element={<DashboardKeluarga />} /> */}
          <Route path="databerita" element={<DashboardBerita />} />
          <Route path="tambahberita" element={<Editor />} />
          {/* <Route path="tambahberita" element={<DashboardBerita />} /> */}
        </Route>

        <Route path="chatbot" element={<ChatBot />} />
        <Route path="chatbot/:id" element={<ChatBot />} />

        <Route path="/" element={<SharedLayoutLanding />}>
          <Route index element={<Landing />} />
          <Route path="activities" element={<Activities />} />
          <Route path="article" element={<NewsLanding />} />
          <Route path="semuaBerita" element={<SemuaBerita />} />
          <Route path="berita/:id" element={<OpenSingleBerita />} />
        </Route>
        <Route path="/login" element={<Login />} />
        <Route path="/daftar" element={<Daftar />} />
        <Route path="*" element={<Error />} />
      </Routes>
    </>
  );
}

export default App;
