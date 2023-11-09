import { createSlice } from "@reduxjs/toolkit";

const initialState = {
  page: { name: "Settings" },
  userStatus: _AS.userStatus,
  systemStatus: _AS.systemStatus,
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
  },
});

export const { setPage, setOnBoarding, setUserShops } = userSlice.actions;

export default userSlice.reducer;
