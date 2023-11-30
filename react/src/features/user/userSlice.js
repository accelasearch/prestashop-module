import { createSlice } from "@reduxjs/toolkit";
import { t } from "../../utils";

const initialState = {
  page: { name: t("Settings") },
  userStatus: _AS?.userStatus,
  systemStatus: _AS?.systemStatus,
};

export const userSlice = createSlice({
  name: "user",
  initialState,
  reducers: {
    setPage: (state, action) => {
      state.page = action.payload;
    },
    setOnBoarding: (state, action) => {
      state.userStatus.onBoarding = action.payload;
    },
    setUserShops: (state, action) => {
      state.userStatus.shops = action.payload;
    },
    setLocks: (state, action) => {
      state.systemStatus.locks = action.payload;
    },
  },
});

export const { setPage, setOnBoarding, setUserShops, setLocks } = userSlice.actions;

export default userSlice.reducer;
