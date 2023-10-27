import { createSlice } from "@reduxjs/toolkit";

const initialState = {
  foo: "bar",
};

export const fooSlice = createSlice({
  name: "foo",
  initialState,
  reducers: {
    setFoo: (state, action) => {
      state.foo = action.payload;
    },
  },
});

export const { setFoo } = fooSlice.actions;

export default fooSlice.reducer;